<?php

namespace App\Repository;

use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trip>
 */
class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    public function getFilteredTrips($filters, $user)
    {
        $qb = $this->createQueryBuilder('t');

        // Jointures pour les relations (organisateur, site, inscriptions)
        $qb->leftJoin('t.triOrganiser', 'o') // Organisateur
        ->leftJoin('t.triSite', 's')      // Site
        ->leftJoin('t.triSubscribes', 'sub') // Inscriptions
        ->leftJoin('sub.subParticipantId', 'p'); // Joindre les participants inscrits

        // Appliquer les filtres
        $this->applySiteFilter($qb, $filters);
        $this->applyNameFilter($qb, $filters);
        $this->applyDateFilters($qb, $filters);
        $this->applyOrganisatorAndSubscriptionAndAncientFilters($qb, $filters, $user);

        return $qb->getQuery()->getResult();
    }

    private function applySiteFilter($qb, $filters)
    {
        if (!empty($filters['site'])) {
            $qb->andWhere('t.triSite = :site')
                ->setParameter('site', $filters['site']);
        }
    }

    private function applyNameFilter($qb, $filters)
    {
        if (!empty($filters['searchName'])) {
            $qb->andWhere('LOWER(t.triName) LIKE :searchName')
                ->setParameter('searchName', '%' . strtolower($filters['searchName']) . '%');
        }
    }

    private function applyDateFilters($qb, $filters)
    {
        if (!empty($filters['startDate'])) {
            $qb->andWhere('t.triStartingDate >= :startDate')
                ->setParameter('startDate', $filters['startDate']);
        }

        if (!empty($filters['endDate'])) {
            $qb->andWhere('t.triClosingDate <= :endDate')
                ->setParameter('endDate', $filters['endDate']);
        }
    }

    private function applyOrganisatorAndSubscriptionAndAncientFilters($qb, $filters, $user)
    {
        // Initialisation des conditions et des paramètres
        $conditions = [];
        $params = [];

        // Conditions basées sur le filtre "organisator"
        if ($filters['organisatorTrip'] !== false) {
            $conditions[] = 'o.parId = :userId';
            $params['userId'] = $user->getId();  // Ajoute userId pour l'organisateur
        }

        // Vérification des filtres d'inscription
        $isSubcribed = $filters['subcribeTrip'] !== false;
        $isNotSubcribed = $filters['notSubcribeTrip'] !== false;

        // Si les deux filtres d'inscription sont activés, ne rien filtrer
        if ($isSubcribed && $isNotSubcribed) {
            $qb->andWhere('1 = 1'); // Cela ne filtre rien
        } elseif ($isSubcribed) {
            // Condition pour "inscrit"
            $conditions[] = 'sub.subParticipantId = :userId';
            $params['userId'] = $user->getId();
        } elseif ($isNotSubcribed) {
            // Condition pour "non inscrit"
            $conditions[] = '(sub.subParticipantId IS NULL OR sub.subParticipantId != :userId)';
            $params['userId'] = $user->getId(); // Assure que le paramètre userId est lié
        }

        // Si le filtre des "Sorties passées" est activé
        if ($filters['ancientTrip'] !== false) {
            $conditions[] = 't.triStartingDate < :now';
            $params['now'] = new \DateTime();
        }

        // Construire la requête en s'assurant de l'absence de conflits
        if (!empty($conditions)) {
            // Utiliser "AND" pour lier toutes les conditions
            $qb->andWhere(implode(' AND ', $conditions));

            // Appliquer les paramètres
            foreach ($params as $key => $value) {
                $qb->setParameter($key, $value);
            }
        }
    }









    //    /**
    //     * @return Trip[] Returns an array of Trip objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Trip
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

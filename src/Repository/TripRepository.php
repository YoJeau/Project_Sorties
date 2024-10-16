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
        $this->applyOrganisatorFilter($qb, $filters);
        $this->applySubscriptionFilters($qb, $filters, $user);
        $this->applyAncientTripFilter($qb, $filters);

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

    private function applyOrganisatorFilter($qb, $filters)
    {
        if ($filters['organisatorTrip'] !== false) {
            $qb->andWhere('o.parId = :organisatorTrip')
                ->setParameter('organisatorTrip', $filters['organisatorTrip']); // Assurez-vous d'utiliser la valeur correcte ici
        }
    }

    private function applySubscriptionFilters($qb, $filters, $user)
    {
        if ($filters['subcribeTrip'] !== false && $filters['notSubcribeTrip'] !== false) {
            // Si les deux filtres sont sélectionnés, ne rien filtrer ou définir une condition par défaut
            $qb->andWhere('1 = 1'); // Cela ne filtre rien
        } elseif ($filters['subcribeTrip'] !== false) {
            // Vérifier si l'utilisateur est inscrit
            $qb->andWhere('sub.subParticipantId = :userId')
                ->setParameter('userId', $user->getId());
        } elseif ($filters['notSubcribeTrip'] !== false) {
            // Vérifier si l'utilisateur n'est pas inscrit
            $qb->andWhere('sub.subParticipantId IS NULL OR sub.subParticipantId != :userId')
                ->setParameter('userId', $user->getId());
        }
    }

    private function applyAncientTripFilter($qb, $filters)
    {
        if ($filters['ancientTrip'] !== false) {
            $qb->andWhere('t.triClosingDate < :now')
                ->setParameter('now', new \DateTime());
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

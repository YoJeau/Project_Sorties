<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Trip;

class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    public function findNonArchivedTrips(){
        return $this->createQueryBuilder('t')
            ->join('t.triState','s')
            ->where('s.staLabel != :state')
            ->setParameter('state','Archivée')
            ->getQuery()
            ->getResult();
    }
    public function getFilteredTrips($filters, $user)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->join('t.triState', 's');
        $this->applyOrganizerAndSubscriptionFilters($qb, $filters, $user);

        if ($filters['ancientTrip'] === true) {
            $qb->orWhere('t.triStartingDate < :now')
                ->setParameter('now', new \DateTime());
        }

        $this->applyNameFilter($qb, $filters);
        $this->applyDateFilters($qb, $filters);
        $this->applySiteFilter($qb, $filters);
        $qb->andWhere('s.staLabel != :archived') // Exclure les trips archivés
            ->setParameter('archived', 'Archivée'); // Remplacez 'archivé' par la valeur appropriée dans votre base de données

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
            $qb->andWhere('t.triName LIKE :searchName')
                ->setParameter('searchName', '%' . $filters['searchName'] . '%');
        }
    }

    private function applyDateFilters($qb, $filters)
    {
        if (!empty($filters['startDate'])) {
            $qb->andWhere('t.triStartingDate >= :startDate')
                ->setParameter('startDate', $filters['startDate']);
        }

        if (!empty($filters['endDate'])) {
            $qb->andWhere('t.triStartingDate <= :endDate')
                ->setParameter('endDate', $filters['endDate']);
        }
    }

    private function applyOrganizerAndSubscriptionFilters($qb, $filters, $user)
    {

        if ($filters['subcribeTrip'] === true && $filters['notSubcribeTrip'] === true) {
            return $qb->getQuery()->getResult();
        }

        // Filtrer les voyages organisés par l'utilisateur
        if ($filters['organisatorTrip'] === true) {
            $qb->orWhere('t.triOrganiser = :user')
                ->setParameter('user', $user);
        }

        // Voyages auxquels l'utilisateur est inscrit
        if ($filters['subcribeTrip'] === true) {
            $qb->join('t.triSubscribes', 's1')
                ->orWhere('s1.subParticipantId = :user')
                ->setParameter('user', $user);
        }

        // Voyages auxquels l'utilisateur n'est pas inscrit et n'est pas l'organisateur
        if ($filters['notSubcribeTrip'] === true) {
            // Effectuer une jointure gauche pour vérifier les abonnements
            $qb->leftJoin('t.triSubscribes', 's2', 'WITH', 's2.subParticipantId = :user')
                ->orWhere('s2.subParticipantId IS NULL');

            // Si l'utilisateur est un organisateur, on ne filtre pas par organisateur
            if ($filters['organisatorTrip'] === true) {
                // Pas de condition andWhere sur l'organisateur
                $qb->setParameter('user', $user);
            } else {
                // Si pas organisateur, alors on filtre pour ne pas inclure les voyages organisés par l'utilisateur
                $qb->andWhere('t.triOrganiser != :user')
                    ->setParameter('user', $user);
            }
        }
    }
}

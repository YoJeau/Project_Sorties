<?php

namespace App\Repository;

use App\Entity\State;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Trip;

class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    public function findNonArchivedTrips()
    {
        return $this->createQueryBuilder('t')
            ->join('t.triState', 's')
            ->where('s.staLabel != :state')
            ->setParameter('state', State::STATE_ARCHIVED)
            ->getQuery()
            ->getResult();
    }

    public function getFilteredTrips($filters, $user)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->join('t.triState', 's');
        $this->applyOrganizerAndSubscriptionFilters($qb, $filters, $user);

        $this->applyNameFilter($qb, $filters);
        $this->applyDateFilters($qb, $filters);
        $this->applySiteFilter($qb, $filters);
        $qb->andWhere('s.staLabel != :archived')
            ->setParameter('archived', State::STATE_ARCHIVED);

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

    private function applyOrganizerAndSubscriptionFilters($qb, $filters, $user): void
    {
        // Paramètre pour la date actuelle
        $now = new \DateTime();

        // Cas 1 : Tous les filtres sont vrais (on retourne tous les résultats)
        if ($filters['organisatorTrip'] && $filters['subcribeTrip'] && $filters['notSubcribeTrip'] && $filters['ancientTrip']) {
            return; // Retourner tous les résultats, pas besoin d'ajouter de conditions
        }

        // Cas 2 : Organisateur + Inscrit + Pas Inscrit
        if ($filters['organisatorTrip'] && $filters['subcribeTrip'] && $filters['notSubcribeTrip']) {
            $qb->orWhere('t.triOrganiser = :user')
                ->leftJoin('t.triSubscribes', 'subInscrit')
                ->orWhere('subInscrit.subParticipantId = :user')
                ->orWhere('subInscrit.subParticipantId IS NULL');
            $qb->setParameter('user', $user);
            return;
        }

        // Cas 3 : Organisateur + Inscrit
        if ($filters['organisatorTrip'] && $filters['subcribeTrip']) {
            $qb->orWhere('t.triOrganiser = :user')
                ->leftJoin('t.triSubscribes', 'subInscrit')
                ->orWhere('subInscrit.subParticipantId = :user');
            $qb->setParameter('user', $user);
            return;
        }

        // Cas 4 : Organisateur + Pas Inscrit
        if ($filters['organisatorTrip'] && $filters['notSubcribeTrip']) {
            $qb->andWhere('t.triOrganiser = :user')
                ->leftJoin('t.triSubscribes', 'notInscrit', 'WITH', 'notInscrit.subParticipantId = :user')
                ->orWhere('notInscrit.subParticipantId IS NULL');
            $qb->setParameter('user', $user);
            return;
        }

        // Cas 5 : Inscrit + Pas Inscrit
        if ($filters['subcribeTrip'] && $filters['notSubcribeTrip']) {
            $qb->leftJoin('t.triSubscribes', 'notInscrit1', 'WITH', 'notInscrit1.subParticipantId = :user')
                ->orWhere('notInscrit1.subParticipantId IS NULL')
                ->orWhere('notInscrit1.subParticipantId = :user')
                ->andWhere('t.triOrganiser != :user');
            $qb->setParameter('user', $user);
            return;
        }

        // Cas 6 : Pas inscrit
        if ($filters['notSubcribeTrip']) {
            $qb->leftJoin('t.triSubscribes', 'notInscrit', 'WITH', 'notInscrit.subParticipantId = :user')
                ->orWhere('notInscrit.subParticipantId IS NULL');

            if (!$filters['organisatorTrip']) {
                $qb->andWhere('t.triOrganiser != :user');
            }
            $qb->setParameter('user', $user);
            return;
        }

        // Cas 7 : Ancien
        if ($filters['ancientTrip']) {
            $qb->orWhere('t.triStartingDate < :now')
                ->setParameter('now', $now);
            return;
        }

        // Cas 8 : Organisateur
        if ($filters['organisatorTrip']) {
            $qb->andWhere('t.triOrganiser = :user');
            $qb->setParameter('user', $user);
            return;
        }

        // Cas 9 : Inscrit
        if ($filters['subcribeTrip']) {
            $qb->join('t.triSubscribes', 'subInscrit')
                ->orWhere('subInscrit.subParticipantId = :user');
            $qb->setParameter('user', $user);
            return;
        }
    }
}

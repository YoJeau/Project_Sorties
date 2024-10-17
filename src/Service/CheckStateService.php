<?php

namespace App\Service;

use App\Repository\StateRepository;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;

class CheckStateService
{
    public function __construct(TripRepository $tripRepository, StateRepository $stateRepository, EntityManagerInterface $entityManager)
    {
        $this->tripRepository = $tripRepository;
        $this->stateRepository = $stateRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function checkState()
    {
        // Définir la zone horaire par défaut
        $timezone = new \DateTimeZone('Europe/Paris');

        $trips = $this->tripRepository->findAll();
        foreach ($trips as $trip) {
            $startDate = $trip->getTriStartingDate();
            $endDate = $trip->getTriClosingDate();
            $state = $trip->getTriState()->getStaLabel();
            $startDate = new \DateTimeImmutable($startDate->format('Y-m-d H:i:s'), $timezone);
            $endDate = new \DateTimeImmutable($endDate->format('Y-m-d H:i:s'), $timezone);



            // Ignorer les trips en "En création", "Terminée" ou "Annulée"
            if ($state === 'En Création' || $state === 'Annulée') {
                continue;
            }

            // Vérifications d'état

            $this->checkOpenState($trip,$startDate, $timezone);
            $this->checkInProgressState($trip, $startDate, $timezone);
            $this->checkedClosedState($trip, $startDate, $endDate, $timezone);
        }
        $this->entityManager->flush();
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function checkInProgressState($trip, $startDate, $timezone)
    {
        // Créer une date actuelle avec le fuseau horaire Europe/Paris
        $currentDate = new \DateTimeImmutable('now', $timezone);
        // Récupérer la durée du trip en minutes
        $duration = $trip->getTriDuration(); // Durée en minutes
        $endDate = $startDate->modify("+{$duration} minutes");


        if ($currentDate >= $startDate && $currentDate <= $endDate) {
            $this->updateTripState($trip, 'En Cours');
        }

        // Vérification de l'état "Terminée"
        if ($currentDate > $endDate) {
            $this->updateTripState($trip, 'Terminée');
        }
    }


    public function checkOpenState($trip, $startDate, $timezone)
    {
        $maxSubcribe = $trip->getTriMaxInscriptionNumber();
        $subscribes = count($trip->getTriSubscribes());
        $isFull = $maxSubcribe == $subscribes;
        $currentDate = new \DateTimeImmutable('now', $timezone);
        if ($currentDate < $startDate && !$isFull) {
            $this->updateTripState($trip, 'Ouverte');
        }

        if ($currentDate < $startDate && $isFull) {
            $this->updateTripState($trip, 'Fermée');
        }
    }

    public function checkedClosedState($trip, $startDate, $endDate, $timezone)
    {
        $currentDate = new \DateTimeImmutable('now', $timezone);
        if ($currentDate > $endDate && $currentDate < $startDate) {
            $this->updateTripState($trip, 'Clôturée');
        }
    }

    private function updateTripState($trip, $newStateLabel)
    {
        $newState = $this->stateRepository->findOneBy(['staLabel' => $newStateLabel]);
        if ($newState) {
            $trip->setTriState($newState);
        }
    }
}
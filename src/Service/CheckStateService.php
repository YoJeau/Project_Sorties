<?php

namespace App\Service;

use App\Entity\State;
use App\Entity\Trip;
use App\Repository\StateRepository;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;

class CheckStateService
{
    private TripRepository $tripRepository;
    private StateRepository $stateRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(TripRepository $tripRepository, StateRepository $stateRepository, EntityManagerInterface $entityManager)
    {
        $this->tripRepository = $tripRepository;
        $this->stateRepository = $stateRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function checkState(): void
    {
        $trips = $this->tripRepository->findAll();
        foreach ($trips as $trip) {
            $state = $trip->getTriState()->getStaLabel();

            // Ignorer les trips en "En création", "Terminée" ou "Annulée"
            if (
                $state !== State::STATE_CREATED
                &&
                $state !== State::STATE_CANCELLED
                &&
                $state !== State::STATE_ARCHIVED
            ) {
                $this->checkTripState($trip);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * checks the state of the trip in parameter.
     *
     * @param Trip $trip
     * @return void
     * @throws \DateInvalidOperationException
     * @throws \DateMalformedStringException
     */
    public function checkTripState(Trip $trip): void
    {
        // sets the default time zone
        $timezone = new \DateTimeZone('Europe/Paris');

        $startDate = $trip->getTriStartingDate();
        $endDate = $trip->getTriClosingDate();
        $startDate = new \DateTimeImmutable($startDate->format('Y-m-d H:i:s'), $timezone);
        $endDate = new \DateTimeImmutable($endDate->format('Y-m-d H:i:s'), $timezone);

        // status checks
        $this->checkOpenState($trip,$startDate, $timezone);
        $this->checkInProgressState($trip, $startDate, $timezone);
        $this->checkedClosedState($trip, $startDate, $endDate, $timezone);
        $this->checkedArchiveTrip($trip,$startDate,$timezone);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function checkInProgressState($trip, $startDate, $timezone): void
    {
        // Créer une date actuelle avec le fuseau horaire Europe/Paris
        $currentDate = new \DateTimeImmutable('now', $timezone);
        // Récupérer la durée du trip en minutes
        $duration = $trip->getTriDuration(); // Durée en minutes
        $endDate = $startDate->modify("+{$duration} minutes");

        if ($currentDate >= $startDate && $currentDate <= $endDate) {
            $this->updateTripState($trip, State::STATE_IN_PROGRESS);
        }

        // Vérification de l'état "Terminée"
        if ($currentDate > $endDate) {
            $this->updateTripState($trip, State::STATE_COMPLETED);
        }
    }

    public function checkOpenState($trip, $startDate, $timezone): void
    {
        $maxSubcribe = $trip->getTriMaxInscriptionNumber();
        $subscribes = count($trip->getTriSubscribes());
        $isFull = $maxSubcribe == $subscribes;
        $currentDate = new \DateTimeImmutable('now', $timezone);
        if ($currentDate < $startDate && !$isFull) {
            $this->updateTripState($trip, State::STATE_OPEN);
        }

        if ($currentDate < $startDate && $isFull) {
            $this->updateTripState($trip, State::STATE_CLOSED);
        }
    }

    public function checkedClosedState($trip, $startDate, $endDate, $timezone): void
    {
        $currentDate = new \DateTimeImmutable('now', $timezone);
        if ($currentDate > $endDate && $currentDate < $startDate) {
            $this->updateTripState($trip, State::STATE_CLOSED_SUBSCRIBE);
        }
    }

    /**
     * @throws \DateMalformedStringException
     * @throws \DateInvalidOperationException
     */
    public function checkedArchiveTrip($trip, $startDate, $timezone): void
    {
        $currentDate = new \DateTimeImmutable('now', $timezone);
        $month = new \DateInterval('P1M');
        $limitDate = $currentDate->sub($month);

        if($startDate < $limitDate){
            $this->updateTripState($trip,State::STATE_ARCHIVED);
        }

    }

    private function updateTripState($trip, $newStateLabel): void
    {
        $newState = $this->stateRepository->findOneBy(['staLabel' => $newStateLabel]);
        if ($newState) {
            $trip->setTriState($newState);
        }
    }
}
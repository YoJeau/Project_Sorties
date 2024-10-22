<?php

namespace App\Service;

use App\Entity\State;
use App\Repository\StateRepository;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class StateService
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
            if ($state === State::STATE_CREATED || $state === State::STATE_CANCELLED || $state === State::STATE_ARCHIVED) {
                continue;
            }

            // Vérifications d'état

            $this->checkOpenState($trip,$startDate, $timezone);
            $this->checkInProgressState($trip, $startDate, $timezone);
            $this->checkedClosedState($trip, $startDate, $endDate, $timezone);
            $this->checkedArchiveTrip($trip,$startDate,$timezone);
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
            $this->updateTripState($trip, State::STATE_IN_PROGRESS);
        }

        // Vérification de l'état "Terminée"
        if ($currentDate > $endDate) {
            $this->updateTripState($trip, State::STATE_COMPLETED);
        }
    }


    public function checkOpenState($trip, $startDate, $timezone)
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

    public function checkedClosedState($trip, $startDate, $endDate, $timezone)
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
    public function checkedArchiveTrip($trip, $startDate, $timezone){
        $currentDate = new \DateTimeImmutable('now', $timezone);
        $month = new \DateInterval('P1M');
        $limitDate = $currentDate->sub($month);

        if($startDate < $limitDate){
            $this->updateTripState($trip,State::STATE_ARCHIVED);
        }

    }

    public function getStateForm(Request $request): ?State
    {
        // Vérifier si un état est fourni dans la requête
        if (isset($request->get('trip')['state'])) {
            $stateLabel = State::STATE_OPEN; // État "Ouvert"
        } else {
            $stateLabel = State::STATE_CREATED; // État "Créé"
        }

        // Rechercher l'état dans la base de données
        return $this->stateRepository->findOneBy(['staLabel' => $stateLabel]);
    }

    private function updateTripState($trip, $newStateLabel)
    {
        $newState = $this->stateRepository->findOneBy(['staLabel' => $newStateLabel]);
        if ($newState) {
            $trip->setTriState($newState);
        }
    }
}
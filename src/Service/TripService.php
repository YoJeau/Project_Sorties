<?php

namespace App\Service;

use App\Entity\Location;
use App\Entity\Participant;
use App\Entity\State;
use App\Entity\Trip;
use Doctrine\ORM\EntityManagerInterface;

class TripService
{

    public function __construct(private EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    public function checkUpdate( Trip $trip, Participant $participant){
        $isValid = true ;
        $isValid &= $this->checkOrganiser($trip, $participant);
        $isValid &= $this->checkStateTrip($trip);

        if($isValid)return true;
        return false;
    }

    public function createTrip(Trip $trip, Location $location, Participant $participant, $state)
    {
        // Associer le lieu au voyage
        $trip->setTriLocation($location);

        // Associer l'état et l'organisateur
        $trip->setTriState($state);
        $trip->setTriOrganiser($participant);

        // Sauvegarder le voyage
        $this->entityManager->persist($trip);

        return $trip;
    }

    private function checkOrganiser(Trip $trip, Participant $participant){
        if($trip->getTriOrganiser()->getId() != $participant->getId()) return false;
        return true;
    }

    private function checkStateTrip(Trip $trip){
        if($trip->getTriState()->getStaLabel() != State::STATE_CREATED) return false;
        return true;
    }

    public function checkDateTripForm(Trip $trip){
        if($trip->getTriClosingDate() > $trip->getTriStartingDate()) return false;
        return true;
    }
}
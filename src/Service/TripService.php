<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\State;
use App\Entity\Trip;

class TripService
{
    public function checkUpdate( Trip $trip, Participant $participant){
        $isValid = true ;
        $isValid &= $this->checkOrganiser($trip, $participant);
        $isValid &= $this->checkStateTrip($trip);

        if($isValid)return true;
        return false;
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
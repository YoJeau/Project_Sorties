<?php

namespace App\Service;

use App\Repository\TripRepository;

class FilterService
{
    public function __construct(TripRepository $tripRepository){
        $this->tripRepository = $tripRepository;
    }

    public function filterTrips($filters,$user){
        $fileredTrips = $this->tripRepository->getFilteredTrips($filters,$user);
        return $fileredTrips;
    }
}
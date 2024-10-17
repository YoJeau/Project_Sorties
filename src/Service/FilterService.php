<?php

namespace App\Service;

use App\Repository\TripRepository;

class FilterService
{
    private TripRepository $tripRepository;

    public function __construct(TripRepository $tripRepository)
    {
        $this->tripRepository = $tripRepository;
    }

    public function filterTrips($filters, $user)
    {
        $filteredTrips = $this->tripRepository->getFilteredTrips($filters, $user);
        return $filteredTrips;
    }

}
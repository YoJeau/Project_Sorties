<?php
namespace App\Service;

use App\Entity\Location;
use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LocationRepository;

class LocationService
{
    private $entityManager;
    private $locationRepository;

    public function __construct(EntityManagerInterface $entityManager, LocationRepository $locationRepository)
    {
        $this->entityManager = $entityManager;
        $this->locationRepository = $locationRepository;
    }

    public function handleLocationChange(Location $currentLocation, Location $newLocation): Location
    {
        if ($newLocation !== $currentLocation) {
            // Persister la nouvelle localisation si elle est différente
            $this->entityManager->persist($newLocation);
            return $newLocation;
        }

        // Si la localisation n'a pas changé, retourner la localisation actuelle
        return $currentLocation;
    }
}


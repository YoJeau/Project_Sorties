<?php
namespace App\Service;

use App\Entity\City;
use App\Entity\Location;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;

class LocationService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LocationRepository $locationRepository)
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

    public function isCityLinkedToLocation (City $city):bool{
        $cityLinked = $this->locationRepository->findOneBy(['locCity' => $city->getId()]);
        if($cityLinked === null) return true;
        return false;
    }
}


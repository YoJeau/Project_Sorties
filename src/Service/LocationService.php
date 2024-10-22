<?php
namespace App\Service;

use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;

class LocationService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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


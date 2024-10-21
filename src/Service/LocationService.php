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

    public function createOrUpdateLocation(array $locationData, City $city = null): ?Location
    {
        // Vérification des données de localisation
        if (!empty($locationData['locName']) || !empty($locationData['locStreet']) ||
            !empty($locationData['locLatitude']) || !empty($locationData['locLongitude'])) {

            $location = new Location();
            $location->setLocName($locationData['locName']);
            $location->setLocStreet($locationData['locStreet']);
            $location->setLocLatitude($locationData['locLatitude']);
            $location->setLocLongitude($locationData['locLongitude']);

            if ($city) {
                $location->setLocCity($city);
            }

            $this->entityManager->persist($location);

            return $location;
        }

        return null;  // Retourne null si aucune donnée valide n'est fournie
    }

    public function getExistingLocation($locationId): ?Location
    {
        return $this->locationRepository->find($locationId);
    }
}


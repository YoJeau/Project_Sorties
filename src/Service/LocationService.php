<?php
namespace App\Service;

use App\Entity\Location;
use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class LocationService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function checkAddNewLocation(Request $request): Location{

    }

    private function updateLocationWithCity(Location $location, City $city): Location
    {
        // Associe la ville à l'emplacement
        $location->setLocCity($city);

        // Persiste et sauvegarde la mise à jour
//        $this->entityManager->persist($location);
        return $location;
    }
}

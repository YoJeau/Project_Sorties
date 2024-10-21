<?php

namespace App\Service;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CityRepository;

class CityService
{
    private $entityManager;
    private $cityRepository;

    public function __construct(EntityManagerInterface $entityManager, CityRepository $cityRepository)
    {
        $this->entityManager = $entityManager;
        $this->cityRepository = $cityRepository;
    }

    public function createOrUpdateCity(array $cityData): ?City
    {
        if (!empty($cityData['citName']) && !empty($cityData['citPostCode'])) {
            // Vérifier si la ville existe déjà
            $existingCity = $this->cityRepository->findOneBy([
                'citName' => $cityData['citName'],
                'citPostCode' => $cityData['citPostCode'],
            ]);

            if ($existingCity) {
                return $existingCity;  // Retourner la ville existante
            }

            // Créer une nouvelle ville
            $city = new City();
            $city->setCitName($cityData['citName']);
            $city->setCitPostCode($cityData['citPostCode']);

            $this->entityManager->persist($city);

            return $city;
        }

        return null; // Retourne null si aucune donnée valide n'est fournie
    }
}

<?php

namespace App\Service;

use App\Entity\City;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CityService
{
    private $cityRepository;
    private $entityManager;

    public function __construct(CityRepository $cityRepository, EntityManagerInterface $entityManager)
    {
        $this->cityRepository = $cityRepository;
        $this->entityManager = $entityManager;
    }

    public function findOrCreateCity(Request $request): ?City
    {
        $cityData = $request->get('city');
        if (empty($cityData['citName']) || empty($cityData['citPostCode'])) {
            return null;  // Pas assez d'informations pour traiter la ville
        }

        // Cherche la ville par nom et code postal
        $foundCity = $this->cityRepository->findOneBy([
            'citName' => $cityData['citName'],
            'citPostCode' => $cityData['citPostCode']
        ]);

        // Si la ville n'existe pas, la créer
        if ($foundCity === null) {
            $city = new City();
            $city->setCitName($cityData['citName']);
            $city->setCitPostCode($cityData['citPostCode']);

//            $this->entityManager->persist($city);
            return $city;
        }
        return $foundCity;  // Retourne la ville existante
    }

    public function findCity(Request $request): ?City{
        return $this->cityRepository->findOneBy(
            ['citName' => $request->get('city')['citName'],
                'citPostCode' => $request->get('city')['citPostCode']]
        );
    }
}

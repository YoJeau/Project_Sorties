<?php
namespace App\Service;

use App\Entity\Location;
use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class LocationService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager,CityService $cityService)
    {
        $this->entityManager = $entityManager;
        $this->cityService = $cityService;
    }


    public function handleLocation(Request $request, ?Location $existingLocation): ?Location
    {

        // Récupérer les données de localisation et de ville
        $locData = $request->get('location');
        $cityData = $request->get('city');
        // Vérifier si tous les champs de localisation sont remplis
        if ($this->areLocationFieldsEmpty($locData)) {
            return $existingLocation; // Indique que l'opération a échoué
        }
        // Vérifier la ville (ou la créer si nécessaire)
        $foundCity = $this->cityService->findOrCreateCity($cityData);

        // Si une localisation existe déjà, on l'utilise
        $location = $existingLocation ?? new Location();

        // Mettre à jour les données de la localisation
        $this->updateLocationFields($location, $locData);

        // Assigner la ville à la localisation
        if ($foundCity) {
            $location->setLocCity($foundCity);
        }

        // Persist the location
        $this->entityManager->persist($location);

        return $location;
    }

    /**
     * Vérifie si tous les champs de localisation sont vides.
     */
    private function areLocationFieldsEmpty(array $locData): bool
    {
        return empty($locData['locName']) || empty($locData['locStreet']) ||
            empty($locData['locLatitude']) || empty($locData['locLongitude']);
    }

    /**
     * Met à jour les champs de la localisation.
     */
    private function updateLocationFields(Location $location, array $locData): void
    {
        if (!empty($locData['locName'])) {
            $location->setLocName($locData['locName']);
        }
        if (!empty($locData['locStreet'])) {
            $location->setLocStreet($locData['locStreet']);
        }
        if (!empty($locData['locLatitude'])) {
            $location->setLocLatitude((float)$locData['locLatitude']);
        }
        if (!empty($locData['locLongitude'])) {
            $location->setLocLongitude((float)$locData['locLongitude']);
        }
    }

    public function isNewLocationDifferent(Location $existingLocation, string $locName, string $locStreet, string $locCity, string $locLatitude, string $locLongitude): bool {
        return (
            $existingLocation->getLocName() !== $locName ||
            $existingLocation->getLocStreet() !== $locStreet ||
            $existingLocation->getLocCity() !== $locCity ||
            $existingLocation->getLocLatitude() !== $locLatitude ||
            $existingLocation->getLocLongitude() !== $locLongitude
        );
    }

}

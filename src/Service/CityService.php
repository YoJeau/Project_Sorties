<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\Location;
use App\Entity\Trip;
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

    /**
     * Vérifie si au moins un champ de la ville contient des données
     *
     * @param array $cityData
     * @return bool
     */
    public function isCityDataValid(array $cityData): bool
    {
        // Liste des champs à vérifier
        $fieldsToCheck = ['locName', 'locStreet', 'locLatitude', 'locLongitude', 'citName', 'citPostCode'];

        // Parcourir les champs et vérifier si l'un d'entre eux contient une donnée non vide
        foreach ($fieldsToCheck as $field) {
            if (!empty($cityData[$field])) {
                return true;
            }
        }

        // Si aucun des champs n'est rempli, on retourne false
        return false;
    }

    /**
     * Gère la création d'une ville à partir des données fournies et l'associe à une localisation.
     *
     * Cette méthode vérifie d'abord si les données de la ville sont fournies.
     * Si c'est le cas, elle cherche à savoir si la ville existe déjà dans la base de données.
     * Si elle n'existe pas, une nouvelle ville est créée et associée à la localisation fournie.
     * Si elle existe, la localisation est simplement mise à jour pour utiliser la ville existante.
     *
     * Fonctionnalités principales :
     * - Récupère le nom et le code postal de la ville à partir des données du tableau.
     * - Vérifie si des données de ville sont présentes avant de continuer.
     * - Rechercher une ville existante dans la base de données.
     * - Créer une nouvelle ville si aucune ville correspondante n'est trouvée.
     * - Associer la ville (existante ou nouvellement créée) à la localisation donnée.
     * - Persister les modifications dans la base de données.
     *
     * @param array $cityData - Les données de la ville, incluant 'citName' et 'citPostCode'.
     * @param Location $location - L'entité Location à laquelle la ville doit être associée.
     * @param Trip $trip - L'entité Trip dans laquelle la localisation sera mise à jour.
     *
     * Flux de contrôle :
     * 1. Récupère le nom et le code postal de la ville à partir des données fournies.
     * 2. Vérifie si des données valides de ville sont présentes.
     * 3. Recherche une ville existante dans la base de données.
     * 4. Si la ville n'existe pas, crée une nouvelle instance de City.
     * 5. Associe la ville à la localisation.
     * 6. Persiste la localisation dans la base de données.
     */
    public function handleCityCreation(array $cityData, Location $location, Trip $trip): void
    {
        $citName = $cityData['citName'] ?? null;
        $citPostCode = $cityData['citPostCode'] ?? null;

        // Vérifier s'il y a des données pour la ville
        if (!empty($citName) || !empty($citPostCode)) {

            // Vérifier si la ville existe déjà
            $foundCity = $this->cityRepository->findOneBy([
                'citName' => $citName,
                'citPostCode' => $citPostCode,
            ]);

            // Si elle n'existe pas, on la crée
            if ($foundCity === null) {
                $city = new City();
                $city->setCitName($citName);
                $city->setCitPostCode($citPostCode);

                $this->entityManager->persist($city);

                // Assigner la nouvelle ville à la location
                $location->setLocCity($city);
            } else {
                // Si elle existe déjà, on l'associe à la location
                $location->setLocCity($foundCity);
            }
            $trip->setTriLocation($location);
            // Persister la location
            $this->entityManager->persist($location);

        }
    }

    /**
     * Gère le changement d'une ville en fonction de la ville actuelle et de la nouvelle ville fournie.
     *
     * Cette méthode vérifie si la ville actuelle est différente de la nouvelle ville.
     * Si les villes sont différentes, elle cherche à déterminer si la nouvelle ville existe déjà dans la base de données.
     * Si la ville n'existe pas, elle crée une nouvelle instance de la ville.
     * Si la ville existe, elle retourne la ville existante.
     * Si la ville n'a pas changé, elle retourne simplement la ville actuelle.
     *
     * Fonctionnalités principales :
     * - Vérifie si la ville actuelle et la nouvelle ville sont différentes.
     * - Recherche une ville existante dans la base de données.
     * - Crée une nouvelle ville si aucune ville correspondante n'est trouvée.
     * - Retourne soit la nouvelle ville, soit la ville existante, soit la ville actuelle en fonction des conditions.
     *
     * @param City $currentCity - L'entité City actuelle avant la mise à jour.
     * @param City $newCity - L'entité City proposée pour la mise à jour.
     *
     * @return City - L'entité City à utiliser (soit la nouvelle, soit l'existante, soit l'actuelle).
     *
     * Flux de contrôle :
     * 1. Vérifie si les identifiants des deux villes sont différents.
     * 2. Recherche une ville existante en utilisant le nom et le code postal de la nouvelle ville.
     * 3. Si aucune ville n'est trouvée, la nouvelle ville est persistée.
     * 4. Retourne la nouvelle ville si elle a été créée, sinon retourne la ville existante.
     * 5. Si la ville n'a pas changé, retourne simplement la ville actuelle.
     */
    public function handleCityChange(City $currentCity, City $newCity): City
    {
        // Vérifier si la ville a changé
        if ($newCity->getId() !== $currentCity->getId()) {
            // Vérifier si la ville existe déjà
            $foundCity = $this->cityRepository->findOneBy([
                'citName' => $newCity->getCitName(),
                'citPostCode' => $newCity->getCitPostCode(),
            ]);

            if ($foundCity === null) {
                // Créer une nouvelle ville
                $this->entityManager->persist($newCity);
                return $newCity;
            } else {
                // Retourner la ville existante
                return $foundCity;
            }
        }
        // Si la ville n'a pas changé, retourner la ville actuelle
        return $currentCity;
    }
}

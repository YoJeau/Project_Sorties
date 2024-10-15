<?php

// src/DataFixtures/CityFixtures.php
namespace App\DataFixtures;

use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CityFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Liste des villes à insérer
        $citiesData = [
            ['name' => 'Paris', 'postCode' => '75000'],
            ['name' => 'Lyon', 'postCode' => '69000'],
            ['name' => 'Marseille', 'postCode' => '13000'],
            ['name' => 'Bordeaux', 'postCode' => '33000'],
            ['name' => 'Nice', 'postCode' => '06000'],
            ['name' => 'Lille', 'postCode' => '59000'],
            ['name' => 'Toulouse', 'postCode' => '31000'],
            ['name' => 'Nantes', 'postCode' => '44000'],
            ['name' => 'Strasbourg', 'postCode' => '67000'],
            ['name' => 'Montpellier', 'postCode' => '34000'],
        ];

        // Boucle pour créer chaque ville
        foreach ($citiesData as $data) {
            $city = new City();
            $city->setCitName($data['name']);
            $city->setCitPostCode($data['postCode']);

            // Persister l'entité City
            $manager->persist($city);
        }

        // Sauvegarder les changements dans la base de données
        $manager->flush();
    }
}


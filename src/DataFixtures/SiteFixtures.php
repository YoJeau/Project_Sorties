<?php

// src/DataFixtures/SiteFixtures.php
namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Les sites à insérer
        $sitesData = [
            'Quimper',
            'Rennes',
            'Nantes',
            'Niort',
        ];

        // Boucle pour créer chaque site
        foreach ($sitesData as $siteName) {
            $site = new Site();
            $site->setSitName($siteName);

            // Persister l'entité Site
            $manager->persist($site);

            // Optionnel : Ajouter une référence pour l'utiliser dans d'autres fixtures (par exemple pour les Participants)
            $this->addReference('site_' . strtolower($siteName), $site);
        }

        // Sauvegarder les sites dans la base de données
        $manager->flush();
    }
}


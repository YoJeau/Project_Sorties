<?php
// src/DataFixtures/TripFixtures.php

namespace App\DataFixtures;

use App\Entity\Trip;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TripFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Références aux entités existantes
        $states = [
            'state_en_cours' => $this->getReference('state_en_cours'),
            'state_ferme' => $this->getReference('state_ferme'),
            'state_ouvert' => $this->getReference('state_ouvert'),
            'state_en_creation' => $this->getReference('state_en_creation'),
            'state_annule' => $this->getReference('state_annule'),
            'state_termine' => $this->getReference('state_termine'),
            'state_cloture' => $this->getReference('state_cloture'),
        ];

        $locations = [
           0 => $this->getReference('location_tour_eiffel'),
            1 => $this->getReference('location_musee_du_louvre'),
           2 => $this->getReference('location_basilique_de_fourviere'),
          4 => $this->getReference('location_vieux_port'),
        ];


        $participants = [];
        foreach (['john_doe', 'jane_doe', 'alice_smith', 'bob_martin', 'charles_davis', 'emily_jones', 'frank_wilson', 'george_clark', 'hannah_hall', 'ivan_lee', 'julia_scott', 'kyle_adams', 'laura_black', 'michael_brown', 'sara_white', 'nick_green', 'linda_young', 'steven_hall', 'david_king', 'olivia_martinez', 'aaron_lopez', 'maria_harris'] as $username) {
            $participants[] = $this->getReference('participant_' . $username);
        }

        $sites = [
            'site_quimper' => $this->getReference('site_quimper'),
            'site_rennes' => $this->getReference('site_rennes'),
            'site_nantes' => $this->getReference('site_nantes'),
            'site_niort' => $this->getReference('site_niort'),
        ];

        // Exemple de données pour créer des voyages
        for ($i = 1; $i <= 10; $i++) {
            $index = 0;
            if($i < 4)$index++;
            $trip = new Trip();
            $trip->setTriName('Voyage ' . $i)
                ->setTriStartingDate(new \DateTime('+'. $i .' days')) // Date de départ dans le futur
                ->setTriDuration(rand(1, 14)) // Durée aléatoire entre 1 et 14 jours
                ->setTriClosingDate(new \DateTime('+' . ($i + rand(1, 5)) . ' days')) // Date de clôture dans le futur
                ->setTriMaxInscriptionNumber(rand(5, 30)) // Nombre maximum d'inscriptions
                ->setTriDescription('Description du voyage ' . $i)
                ->setTriCancellationReason(null) // Pas de raison de cancellation par défaut
                ->setTriState($states[array_rand($states)]) // État aléatoire
                ->setTriLocation($locations[$index]) // Localisation (ajustez au besoin)
                ->setTriOrganiser($participants[$i]) // Organisateur (ajustez au besoin)
                ->setTriSite($sites[array_rand($sites)]); // Site aléatoire

            $manager->persist($trip);
        }

        // Sauvegarder les voyages dans la base de données
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            StateFixtures::class, // Assurez-vous que StateFixtures est chargé avant
            LocationFixtures::class, // Ajoutez LocationFixtures si nécessaire
            ParticipantFixtures::class, // Ajoutez ParticipantFixtures si nécessaire
            SiteFixtures::class, // Assurez-vous que SiteFixtures est chargé avant
        ];
    }
}

<?php
// src/DataFixtures/StateFixtures.php

namespace App\DataFixtures;

use App\Entity\State;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StateFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $statesData = [
            'state_en_cours' => 'En Cours',
            'state_ferme' => 'Fermé',
            'state_ouvert' => 'Ouvert',
            'state_en_creation' => 'En Création',
            'state_annule' => 'Annulé',
            'state_termine' => 'Terminé',
            'state_cloture' => 'Clôturé',
        ];

        foreach ($statesData as $reference => $name) {
            $state = new State();
            $state->setStaLabel($name);
            // Ajoute la référence ici
            $this->addReference($reference, $state);
            $manager->persist($state);
        }

        $manager->flush();
    }
}

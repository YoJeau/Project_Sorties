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
            'state_ferme' => 'Fermée',
            'state_ouvert' => 'Ouverte',
            'state_en_creation' => 'En Création',
            'state_annule' => 'Annulée',
            'state_termine' => 'Terminée',
            'state_cloture' => 'Clôturée',
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

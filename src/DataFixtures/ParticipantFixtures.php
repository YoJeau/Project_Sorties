<?php
// src/DataFixtures/ParticipantFixtures.php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {


        // Liste de participants à insérer
        $participantsData = [
            ['username' => 'john_doe', 'lastName' => 'Doe', 'firstName' => 'John', 'phone' => '123456789', 'email' => 'john@example.com', 'password' => 'password123', 'isActive' => true,'siteReference' => 'site_quimper'],
            ['username' => 'jane_doe', 'lastName' => 'Doe', 'firstName' => 'Jane', 'phone' => '987654321', 'email' => 'jane@example.com', 'password' => 'password123', 'isActive' => true,'siteReference' => 'site_rennes'],
            ['username' => 'alice_smith', 'lastName' => 'Smith', 'firstName' => 'Alice', 'phone' => '234567890', 'email' => 'alice@example.com', 'password' => 'alicepass', 'isActive' => true,'siteReference' => 'site_nantes'],
            ['username' => 'bob_martin', 'lastName' => 'Martin', 'firstName' => 'Bob', 'phone' => '345678901', 'email' => 'bob@example.com', 'password' => 'bobpass', 'isActive' => true,'siteReference' => 'site_niort'],
            ['username' => 'charles_davis', 'lastName' => 'Davis', 'firstName' => 'Charles', 'phone' => '456789012', 'email' => 'charles@example.com', 'password' => 'charlespass', 'isActive' => false,'siteReference' => 'site_quimper'],
            ['username' => 'emily_jones', 'lastName' => 'Jones', 'firstName' => 'Emily', 'phone' => '567890123', 'email' => 'emily@example.com', 'password' => 'emilypass', 'isActive' => true,'siteReference' => 'site_quimper'],
            ['username' => 'frank_wilson', 'lastName' => 'Wilson', 'firstName' => 'Frank', 'phone' => '678901234', 'email' => 'frank@example.com', 'password' => 'frankpass', 'isActive' => true,'siteReference' => 'site_quimper'],
            ['username' => 'george_clark', 'lastName' => 'Clark', 'firstName' => 'George', 'phone' => '789012345', 'email' => 'george@example.com', 'password' => 'georgepass', 'isActive' => true,'siteReference' => 'site_nantes'],
            ['username' => 'hannah_hall', 'lastName' => 'Hall', 'firstName' => 'Hannah', 'phone' => '890123456', 'email' => 'hannah@example.com', 'password' => 'hannahpass', 'isActive' => false,'siteReference' => 'site_nantes'],
            ['username' => 'ivan_lee', 'lastName' => 'Lee', 'firstName' => 'Ivan', 'phone' => '901234567', 'email' => 'ivan@example.com', 'password' => 'ivanpass', 'isActive' => true,'siteReference' => 'site_nantes'],
            ['username' => 'julia_scott', 'lastName' => 'Scott', 'firstName' => 'Julia', 'phone' => '012345678', 'email' => 'julia@example.com', 'password' => 'juliapass', 'isActive' => true,'siteReference' => 'site_nantes'],
            ['username' => 'kyle_adams', 'lastName' => 'Adams', 'firstName' => 'Kyle', 'phone' => '112345678', 'email' => 'kyle@example.com', 'password' => 'kylepass', 'isActive' => true,'siteReference' => 'site_nantes'],
            // Nouveaux participants
            ['username' => 'laura_black', 'lastName' => 'Black', 'firstName' => 'Laura', 'phone' => '223456789', 'email' => 'laura@example.com', 'password' => 'laurapass', 'isActive' => true,'siteReference' => 'site_nantes'],
            ['username' => 'michael_brown', 'lastName' => 'Brown', 'firstName' => 'Michael', 'phone' => '334567890', 'email' => 'michael@example.com', 'password' => 'michaelpass', 'isActive' => true,'siteReference' => 'site_rennes'],
            ['username' => 'sara_white', 'lastName' => 'White', 'firstName' => 'Sara', 'phone' => '445678901', 'email' => 'sara@example.com', 'password' => 'sarapass', 'isActive' => true,'siteReference' => 'site_rennes'],
            ['username' => 'nick_green', 'lastName' => 'Green', 'firstName' => 'Nick', 'phone' => '556789012', 'email' => 'nick@example.com', 'password' => 'nickpass', 'isActive' => false,'siteReference' => 'site_rennes'],
            ['username' => 'linda_young', 'lastName' => 'Young', 'firstName' => 'Linda', 'phone' => '667890123', 'email' => 'linda@example.com', 'password' => 'lindapass', 'isActive' => true,'siteReference' => 'site_rennes'],
            ['username' => 'steven_hall', 'lastName' => 'Hall', 'firstName' => 'Steven', 'phone' => '778901234', 'email' => 'steven@example.com', 'password' => 'stevenpass', 'isActive' => true,'siteReference' => 'site_rennes'],
            ['username' => 'david_king', 'lastName' => 'King', 'firstName' => 'David', 'phone' => '889012345', 'email' => 'david@example.com', 'password' => 'davidpass', 'isActive' => false,'siteReference' => 'site_rennes'],
            ['username' => 'olivia_martinez', 'lastName' => 'Martinez', 'firstName' => 'Olivia', 'phone' => '990123456', 'email' => 'olivia@example.com', 'password' => 'oliviapass', 'isActive' => true,'siteReference' => 'site_rennes'],
            ['username' => 'aaron_lopez', 'lastName' => 'Lopez', 'firstName' => 'Aaron', 'phone' => '101234567', 'email' => 'aaron@example.com', 'password' => 'aaronpass', 'isActive' => true,'siteReference' => 'site_rennes'],
            ['username' => 'maria_harris', 'lastName' => 'Harris', 'firstName' => 'Maria', 'phone' => '112345678', 'email' => 'maria@example.com', 'password' => 'mariapass', 'isActive' => true,'siteReference' => 'site_rennes'],
        ];

        foreach ($participantsData as $key=>$data) {
            $participant = new Participant();
            $participant->setParUsername($data['username']);
            $participant->setParLastName($data['lastName']);
            $participant->setParFirstName($data['firstName']);
            $participant->setParPhone($data['phone']);
            $participant->setParEmail($data['email']);
            $participant->setParIsActive($data['isActive']);
            $participant->setRoles(["ROLE_USER"]);

            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword(
                $participant,
                $data['password']
            );
            $participant->setPassword($hashedPassword);

            // Ajouter une référence pour le participant
            $this->addReference('participant_' . $data['username'], $participant);

            // Assigner le site à partir de la référence
            $site = $this->getReference($data['siteReference']);
            $participant->setParSite($site);


            // Persist le participant dans l'EntityManager
            $manager->persist($participant);
        }

        // Sauvegarde des modifications dans la base de données
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            // Ajoutez ici les classes de fixtures dont ParticipantFixtures dépend
            StateFixtures::class, // Si vous avez besoin de ces états
            // Ajoutez d'autres dépendances si nécessaire
        ];
    }
}



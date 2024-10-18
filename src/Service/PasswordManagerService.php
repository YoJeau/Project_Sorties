<?php

namespace App\Service;

use App\Entity\Participant;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordManagerService
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function managePassword(Participant $participant, ?string $currentPassword, ?string $plainPassword, ?string $confirmPassword): void
    {
        if ($currentPassword && !$this->passwordHasher->isPasswordValid($participant, $currentPassword)) {
            throw new \RuntimeException("Mot de passe incorrect");
        }
        if ($plainPassword !== $confirmPassword) {
            throw new \RuntimeException("Les mots de passe ne correspondent pas");
        }
        // Hacher le nouveau mot de passe
        $participant->setPassword($this->passwordHasher->hashPassword($participant, $plainPassword));
    }
}
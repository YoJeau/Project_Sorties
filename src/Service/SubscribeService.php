<?php

namespace App\Service;

use App\Entity\State;
use App\Entity\Subscribe;
use App\Repository\SubscribeRepository;
use Doctrine\ORM\EntityManagerInterface;

class SubscribeService
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly SubscribeRepository $subscribeRepository)
    { }

    public function checkSubscribe($trip, $user): bool
    {

        $isPossible = true;
        $isPossible &= $this->isOrganiser($trip, $user);
        $isPossible &= $this->alreadySubscribe($trip, $user);
        $isPossible &= $this->checkMaxSubscribe($trip);
        $isPossible &= $this->checkState($trip);

        if ($isPossible) {
            $subscribe = new Subscribe();
            $subscribe->setSubTripId($trip);
            $subscribe->setSubParticipantId($user);
            $trip->addSubscribe($subscribe);
            $this->entityManager->persist($subscribe);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    public function checkUnsubscribe($trip, $user): bool
    {
        $isPossible = true;

       // on recupère l'inscription si elle existe
        $subscribe= $this->subscribeRepository->findOneBy([
            'subTripId' => $trip,
            'subParticipantId' => $user
        ]);

        // true si existe sinon false
        $isPossible &= $subscribe !== null;
        // si l'état de la sortie permet encore de se désincrire
        $isPossible &= $this->checkState($trip);

        //si toutes les conditions sont a vrai
        if ($isPossible) {
            $trip->removeSubscribe($subscribe);
            $user->removeParSubscribe($subscribe);
            $this->entityManager->remove($subscribe);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    private function alreadySubscribe($trip, $user): bool
    {
        $subscribes = $trip->getTriSubscribes();
        foreach ($subscribes as $subscribe) {
            if ($subscribe->getSubParticipantId() === $user->getId()) {
                return false;
            }
        }
        return true; // Pas déjà abonné, renvoie true
    }

    private function isOrganiser($trip, $user): bool
    {
        if($trip->getTriOrganiser()->getId() === $user->getId()) return false;
        return true;
    }

    private function checkMaxSubscribe($trip): bool
    {
        $maxSubscribe = $trip->getTriMaxInscriptionNumber();
        $countSubscribe = count($trip->getTriSubscribes());
        // Vérifie si le nombre maximum est atteint
        return $countSubscribe < $maxSubscribe; // Renvoie true si le maximum n'est pas atteint
    }

    public function checkState($trip): bool
    {
        $state = $trip->getTriState()->getStaLabel();
        if($state === State::STATE_OPEN || $state === State::STATE_CLOSED || $state === State::STATE_CLOSED_SUBSCRIBE) return true;
        return false;
    }
}

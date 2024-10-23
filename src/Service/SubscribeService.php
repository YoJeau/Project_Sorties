<?php

namespace App\Service;

use App\Entity\State;
use App\Entity\Subscribe;
use App\Repository\SubscribeRepository;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;

class SubscribeService
{
    public function __construct(TripRepository $tripRepository, EntityManagerInterface $entityManager,SubscribeRepository $subscribeRepository){
        $this->tripRepository = $tripRepository;
        $this->entityManager = $entityManager;
        $this->subscribeRepository = $subscribeRepository;
    }

    public function checkSubscribe($trip, $user){

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

    public function checkUnsubscribe($trip, $user){
        $isPossible = true;

        // Récupérer l'abonnement en cours
        $subscribe= $this->subscribeRepository->findOneBy([
            'subTripId' => $trip,
            'subParticipantId' => $user
        ]);

        $isPossible &= $subscribe !== null;
        $isPossible &= $this->isOrganiser($trip, $user);
        $isPossible &= $this->checkState($trip);

        if ($isPossible) {
            $trip->removeSubscribe($subscribe);
            $user->removeParSubscribe($subscribe);
            $this->entityManager->remove($subscribe);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    private function alreadySubscribe($trip, $user){
        $subscribes = $trip->getTriSubscribes();
        foreach ($subscribes as $subscribe) {
            if ($subscribe->getSubParticipantId() === $user->getId()) {
                return false;
            }
        }
        return true; // Pas déjà abonné, renvoie true
    }

    private function isOrganiser($trip, $user){
        if($trip->getTriOrganiser()->getId() === $user->getId()) return false;
        return true;
    }

    private function checkMaxSubscribe($trip){
        $maxSubscribe = $trip->getTriMaxInscriptionNumber();
        $countSubscribe = count($trip->getTriSubscribes());
        // Vérifie si le nombre maximum est atteint
        return $countSubscribe < $maxSubscribe; // Renvoie true si le maximum n'est pas atteint
    }

    public function checkState($trip){
        $state = $trip->getTriState()->getStaLabel();
        if($state === State::STATE_OPEN || $state === State::STATE_CLOSED || $state === State::STATE_CLOSED_SUBSCRIBE) return true;
        return false;
    }
}

<?php

namespace App\Service;

class ActionService
{
    /**
     * Détermine l'action à réaliser pour chaque voyage.
     *
     * @param User $user   L'utilisateur.
     * @param Trip[] $trips Un tableau d'objets Trip.
     */
    public function determineAction($user,$trips){
        $actions = [];
        foreach($trips as $row){
            $isSubcribed = $this->isSubscribed($user,$row);
            $isOrganisator = $this->isOrganisator($user,$row);
            $checkSubcribe = $this->checkSubcribe($row);

            $isClosed = $this->isClosed($row);
            $isClosedSubDate = $this->isClosedSubDate($isClosed,$isSubcribed,$row);
            $isOpenAndSub = $this->isOpenAndSub($isSubcribed,$row);
            $isOpenAndNotSub = $this-> isOpenAndNotSub($isSubcribed,$checkSubcribe,$row);
            $isCreatedAndOrganisator = $this->isCreatedAndOrganisator($isOrganisator,$row);
            $isOpenAndOrganisator = $this->isOpenAndOrganisator($isOrganisator,$row);

            $actions = [
                "tripId"=>$row->getId(),
                "action" => $this->chooseAction($isClosed,$isClosedSubDate,$isOpenAndSub,$isOpenAndNotSub,$isCreatedAndOrganisator,$isOpenAndOrganisator)
            ] ;
        }
        var_dump($actions);
        return $actions;
    }

    public function isSubscribed($user,$row){

        foreach($row->getTriSubscribes() as $subscribes){
            if($subscribes->getSubParticipantId()->getId() == $user->getId()){
                return true;
            }
            return false;
        }
        return false;
    }

    public function isOrganisator($user,$row){
        if($row->getTriOrganiser()->getId() == $user->getId()){
            return true;
        }
        return false;
    }

    public function checkSubcribe($row){
        $currentDate = new \DateTime();
        if($currentDate > $row->getTriClosingDate() || count($row->getTriSubscribes()) == $row->getTriMaxInscriptionNumber()){
            return false;
        }
        return true;
    }

    public function isClosed($row){
        if($row->getTriState()->getStaLabel() === 'Clôturée') return true;
        return false;
    }

    public function isClosedSubDate($isClosed,$isSubcribe,$row){
        $currentDate = new \DateTime();
        if($isClosed && $isSubcribe && $currentDate > $row->getTriClosingDate()) return true;
        return false;
    }

    public function isOpenAndSub($isSubcribe,$row){
        if($isSubcribe && $row->getTriState()->getStaLabel() === 'Ouverte') return true;
        return false;
    }

    public function isOpenAndNotSub($isSubcribe,$checkSubcribe,$row){
        if(!$isSubcribe && !$row->getTriState()->getStaLabel() === 'Ouverte' && $checkSubcribe) return true;
        return false;
    }

    public function isCreatedAndOrganisator($isOrganisator,$row){
        if($isOrganisator && $row->getTriState()->getStaLabel() === 'Créée') return true;
        return false;
    }

    public function isOpenAndOrganisator($isOrganisator,$row){
        if($isOrganisator && $row->getTriState()->getStaLabel() === 'Ouverte') return true;
        return false;
    }

    public function chooseAction($isClosed,$isClosedSubDate,$isOpenAndSub,$isOpenAndNotSub,$isCreatedAndOrganisator,$isOpenAndOrganisator){
        if($isClosedSubDate){
            return "<a href=''> <span class='badge rounded-pill bg-info'>Afficher</span></a>
                        <a href=''> <span class='badge rounded-pill bg-info'> Se désister</span></a>";
        }
        if($isClosed){
            return " <a href=''> <span class='badge rounded-pill bg-info'>Afficher</span></a>";
        }

        if($isOpenAndSub){
            return "<a href=''> <span class='badge rounded-pill bg-info'>Afficher</span></a>
                        <a href=''> <span class='badge rounded-pill bg-info'>Se désister</span></a>";
        }
        if($isOpenAndNotSub){
            return "<a href=''> <span class='badge rounded-pill bg-info'>Afficher</span></a>
                        <a href=''> <span class='badge rounded-pill bg-info'>S'inscrire</span></a>";
        }
        if($isCreatedAndOrganisator){
            return "<a href=''> <span class='badge rounded-pill bg-info'>Modifier</span></a>
                        <a href=''> <span class='badge rounded-pill bg-info'>Publier</span></a>";
        }

        if($isOpenAndOrganisator){
            return "<a href=''> <span class='badge rounded-pill bg-info'>Afficher</span></a>
                        <a href=''> <span class='badge rounded-pill bg-info'>Annuler</span></a>";
        }
        return "toto";
    }
}
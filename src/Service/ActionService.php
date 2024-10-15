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
    public function determineAction($user, $trips): array
    {
        $actions = [];
        foreach ($trips as $row) {
            $isSubcribed = $this->isSubscribed($user, $row);
            $isOrganisator = $this->isOrganisator($user, $row);
            $state = $row->getTriState()->getStaLabel(); // Récupère l'état du voyage

            $actions[] = [
                "tripId" => $row->getId(),
                "action" => $this->chooseAction($state, $isSubcribed, $isOrganisator)
            ];
        }
        return $actions;
    }

    public function isSubscribed($user, $row): bool
    {
        foreach ($row->getTriSubscribes() as $subscribes) {
            if ($subscribes->getSubParticipantId()->getId() == $user->getId()) {
                return true;
            }
        }
        return false;
    }

    public function isOrganisator($user, $row): bool
    {
        return $row->getTriOrganiser()->getId() == $user->getId();
    }

    public function chooseAction($state, $isSubcribed, $isOrganisator)
    {
        switch ($state) {
            case 'Ouverte':
                if ($isSubcribed) {
                    return "<a href=''> <span class='badge rounded-pill bg-info'>Afficher</span></a>
                            <a href=''> <span class='badge rounded-pill bg-info'>Se désister</span></a>";
                } else if ($isOrganisator) {
                    return "<a href=''> <span class='badge rounded-pill bg-info'>Afficher</span></a>
                            <a href=''> <span class='badge rounded-pill bg-info'>Annuler</span></a>";
                }
                else {
                    return "<a href=''> <span class='badge rounded-pill bg-info'>Afficher</span></a>
                            <a href=''> <span class='badge rounded-pill bg-info'>S'inscrire</span></a>";
                }

            case 'Terminée':
            case 'Clôturée':
            case 'Fermée':
                return "<a href=''> <span class='badge rounded-pill bg-info'>Afficher</span></a>";

            case 'En Cours':
                return "<a href=''> <span class='badge rounded-pill bg-info'>Afficher</span></a>";

            case 'En Création':
                if ($isOrganisator) {
                    return "<a href=''> <span class='badge rounded-pill bg-info'>Modifier</span></a>
                            <a href=''> <span class='badge rounded-pill bg-info'>Publier</span></a>";
                }else {
                    return "Aucune action disponible";
                }

            case 'Annulée':
                return "<a href=''> <span class='badge rounded-pill bg-info'>Afficher</span></a>";

            default:
                return "Aucune action disponible"; // Par défaut
        }
    }
}

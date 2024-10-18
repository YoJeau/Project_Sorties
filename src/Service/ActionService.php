<?php
namespace App\Service;

use App\Entity\Participant;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActionService
{

    public function __construct(UrlGeneratorInterface $urlGenerator){
        $this->urlGenerator = $urlGenerator;
    }
    /**
     * Détermine l'action à réaliser pour chaque voyage.
     *
     * @param Participant $user   L'utilisateur.
     * @param PaginationInterface $trips Un tableau d'objets Trip.
     */
    public function determineAction(Participant $user, PaginationInterface $trips): array
    {
        $actions = [];
        foreach ($trips as $row) {
            $isSubcribed = $this->isSubscribed($user, $row);
            $isOrganisator = $this->isOrganisator($user, $row);
            $state = $row->getTriState()->getStaLabel(); // Récupère l'état du voyage

            $actions[] = [
                "tripId" => $row->getId(),
                "action" => $this->chooseAction($state, $isSubcribed, $isOrganisator,$row->getId())
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

    public function generatePath($nameRoute,$param){
        return $this->urlGenerator->generate($nameRoute, ['id' => $param], UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    public function chooseAction($state, $isSubcribed, $isOrganisator, $id)
    {
        // Générer le lien "Afficher" commun à tous les états
        $viewLink = "<a href='' class='text-decoration-none'> <span class='badge rounded-pill bg-info'>Afficher</span></a>";

        // Vérifier l'état pour déterminer les actions possibles
        switch ($state) {
            case 'Ouverte':
                return $this->getOpenStateActions($isSubcribed, $isOrganisator, $id, $viewLink);

            case 'Fermée':
                return $this->getClosedStateActions($isSubcribed, $id, $viewLink);

            case 'Terminée':
            case 'Clôturée':
            case 'En Cours':
            case 'Annulée':
                return $viewLink; // Lien "Afficher" uniquement

            case 'En Création':
                return $this->getCreationStateActions($isOrganisator, $viewLink);

            default:
                return "Aucune action disponible";
        }
    }

    private function getOpenStateActions($isSubcribed, $isOrganisator, $id, $viewLink)
    {
        if ($isSubcribed) {
            // Utilisateur inscrit
            return $viewLink .
                "<a href='".$this->generatePath('app_subscribe_delete', $id)."'  class='text-decoration-none'> <span class='badge rounded-pill bg-info'>Se désister</span></a>";
        } elseif ($isOrganisator) {
            // Organisateur de l'événement
            return $viewLink .
                "<a href=''  class='text-decoration-none'> <span class='badge rounded-pill bg-info'>Annuler</span></a>";
        } else {
            // Pas encore inscrit
            return $viewLink .
                "<a href='".$this->generatePath('app_subscribe', $id)."'  class='text-decoration-none'> <span class='badge rounded-pill bg-info'>S'inscrire</span></a>";
        }
    }

    private function getClosedStateActions($isSubcribed, $id, $viewLink)
    {
        if ($isSubcribed) {
            // Inscrit à un événement fermé
            return $viewLink .
                "<a href='".$this->generatePath('app_subscribe_delete', $id)."'  class='text-decoration-none'> <span class='badge rounded-pill bg-info'>Se désister</span></a>";
        }
        // Seulement le lien "Afficher" si non inscrit
        return $viewLink;
    }

    private function getCreationStateActions($isOrganisator, $viewLink)
    {
        if ($isOrganisator) {
            // L'organisateur peut modifier ou publier
            return "<a href='' class='text-decoration-none'> <span class='badge rounded-pill bg-info'>Modifier</span></a>".
                "<a href='' class='text-decoration-none'> <span class='badge rounded-pill bg-info'>Publier</span></a>";
        }
        return "Aucune action disponible";
    }
}

<?php
namespace App\Service;

use App\Entity\Participant;
use App\Entity\Trip;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActionService
{
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
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

    public function isSubscribed(Participant $user, Trip $row): bool
    {
        foreach ($row->getTriSubscribes() as $subscribes) {
            if ($subscribes->getSubParticipantId()->getId() == $user->getId()) {
                return true;
            }
        }
        return false;
    }

    public function isOrganisator(Participant $user, Trip $row): bool
    {
        return $row->getTriOrganiser()->getId() == $user->getId();
    }

    public function generatePath($nameRoute, $param)
    {
        return $this->urlGenerator->generate($nameRoute, ['id' => $param], UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    public function chooseAction(string $state, bool $isSubcribed, bool $isOrganisator, int $id): string
    {
        // Générer le lien "Afficher" commun à tous les états
        $viewLink = "<a href='' class='text-decoration-none'> <span class='badge rounded-pill bg-info'>Afficher</span></a>";

        // Vérifier l'état pour déterminer les actions possibles
        return match ($state) {
            'Ouverte' => $this->getOpenStateActions($isSubcribed, $isOrganisator, $id, $viewLink),
            'Fermée' => $this->getClosedStateActions($isSubcribed, $id, $viewLink),
            'Terminée', 'Clôturée', 'En Cours', 'Annulée' => $viewLink,
            'En Création' => $this->getCreationStateActions($isOrganisator, $viewLink),
            default => "Aucune action disponible",
        };
    }

    private function getOpenStateActions(bool  $isSubcribed, bool $isOrganisator, int $id, string $viewLink): string
    {
        if ($isSubcribed) {
            // Utilisateur inscrit
            $viewLink .= "<a href='".$this->generatePath('app_subscribe_delete', $id)."'  class='text-decoration-none'> <span class='badge rounded-pill bg-info'>Se désister</span></a>";
        } elseif ($isOrganisator) {
            // Organisateur de l'événement
            $viewLink .= "<a href='" . $this->generatePath('app_trip_cancel', $id) . "'  class='text-decoration-none'> <span class='badge rounded-pill bg-info'>Annuler</span></a>";
        } else {
            // Pas encore inscrit
            $viewLink .= "<a href='".$this->generatePath('app_subscribe', $id)."'  class='text-decoration-none'> <span class='badge rounded-pill bg-info'>S'inscrire</span></a>";
        }

        return $viewLink;
    }

    private function getClosedStateActions(bool  $isSubcribed, int $id, string $viewLink): string
    {
        if ($isSubcribed) {
            // Inscrit à un événement fermé
            $viewLink .= "<a href='".$this->generatePath('app_subscribe_delete', $id)."'  class='text-decoration-none'> <span class='badge rounded-pill bg-info'>Se désister</span></a>";
        }
        // Seulement le lien "Afficher" si non inscrit
        return $viewLink;
    }

    private function getCreationStateActions(bool $isOrganisator, int $id): string
    {
        $viewLink = '';
        if ($isOrganisator) {
            // L'organisateur peut modifier ou publier
            $viewLink .= "<a href='' class='text-decoration-none'> <span class='badge rounded-pill bg-info'>Modifier</span></a>".
                "<a href='" . $this->generatePath('app_subscribe_delete', $id) . "' class='text-decoration-none'> <span class='badge rounded-pill bg-info'>Publier</span></a>";
        } else {
            $viewLink .= "Aucune action disponible";
        }

        return $viewLink;
    }
}

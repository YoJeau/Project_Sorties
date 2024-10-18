<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Location;
use App\Entity\Participant;
use App\Entity\Trip;
use App\Form\CityType;
use App\Form\LocationType;
use App\Form\TripType;
use App\Repository\StateRepository;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/trip', name: 'app_trip')]
class TripController extends AbstractController
{
    #[Route]
    public function index(): Response
    {
        return $this->render('trip/index.html.twig', [
            'controller_name' => 'TripController',
        ]);
    }

    #[Route('/new', name: '_new', methods: ['GET'])]
    public function new(): Response {
        $trip = new Trip();
        $tripForm = $this->createForm(TripType::class, $trip);

        $city = new City();
        $cityForm = $this->createForm(CityType::class, $city);

        $location = new Location();
        $locationForm = $this->createForm(LocationType::class, $location);

        return $this->render('trip/new.html.twig', [
            'tripForm' => $tripForm->createView(),
            'locationForm' => $locationForm->createView(),
            'cityForm' => $cityForm->createView(),
        ]);
    }

    /**
     * Cancel a trip.
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param TripRepository $tripRepository
     * @param StateRepository $stateRepository
     * @param Participant|null $currentParticipant
     * @param int $id Trip id
     * @return Response
     */
    #[Route('/cancel/{id}', name: '_cancel', methods: ['GET', 'POST'])]
    public function cancel(
        Request $request,
        EntityManagerInterface $entityManager,
        TripRepository $tripRepository,
        StateRepository $stateRepository,
        #[CurrentUser] ?Participant $currentParticipant,
        int $id
    ): Response {
        $trip = $tripRepository->find($id);

        if (is_null($trip)) {
            $this->addFlash('danger', "La sortie n'existe pas.");

            return $this->redirectToRoute('app_home');
        }

        if ($currentParticipant && $currentParticipant->getId() !== $trip->getTriOrganiser()->getId()) {
            $this->addFlash('danger', "Vous n'êtes pas l'organisateur de la sortie.");

            return $this->redirectToRoute('app_home');
        }

        if ($trip->getTriState()->getId() !== 3 && $trip->getTriState()->getId() !== 4 && $trip->getTriState()->getId() !== 7) {
            $this->addFlash('danger', "La sortie ne peux pas être annulée.");

            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(TripType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $state = $stateRepository->find(5);
            $trip->setTriState($state);

            $entityManager->flush();
            $this->addFlash('success', 'La sortie a bien été annulée.');

            return $this->redirectToRoute('app_home');
        }
        return $this->render('trip/cancel.html.twig', [
            'trip' => $trip,
            'form' => $form->createView()
        ]);
    }
}

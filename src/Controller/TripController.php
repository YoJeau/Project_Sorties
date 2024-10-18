<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Location;
use App\Entity\Participant;
use App\Entity\State;
use App\Entity\Trip;
use App\Form\CityType;
use App\Form\LocationType;
use App\Form\TripType;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
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
            'tripForm' => $tripForm,
            'locationForm' => $locationForm,
            'cityForm' => $cityForm,
        ]);
    }

    #[Route('/new', name: '_post', methods: ['POST'])]
    public function post(Request $request, EntityManagerInterface $entityManager, StateRepository $stateRepository, CityRepository $cityRepository, #[CurrentUser] Participant $participant) : Response {
        $state = null;

        //Vérification de l'état
        if (isset($request->get('trip')['state'])) {
            $state = $stateRepository->findOneBy(['staLabel' => 'Ouverte']);
        } else {
            $state = $stateRepository->findOneBy(['staLabel' => 'En création']);
        }

        $location = new Location();
        $locationForm = $this->createForm(LocationType::class, $location);
        $locationForm->handleRequest($request);

        $city = new City();
        $cityForm = $this->createForm(CityType::class, $city);
        $cityForm->handleRequest($request);

        $trip = new Trip();
        $tripForm = $this->createForm(TripType::class, $trip);
        $tripForm->handleRequest($request);

        if ($tripForm->isSubmitted() && $tripForm->isValid()) {
            //Vérifier s'il y a des données
            // Cas : l'utilisateur ajoute un lieu et une ville dans le formulaire
            if (!empty($request->get('city')['locName']) || !empty($request->get('city')['locStreet']) ||
                !empty($request->get('city')['locLatitude']) || !empty($request->get('city')['locLongitude']) ||
                !empty($request->get('city')['citName']) || !empty($request->get('city')['citPostCode'])
            ) {

                //Vérifier la saisie de la ville, si elle existe ou pas, pour éviter de la créer en double
                $foundCity = $cityRepository->findOneBy(
                    ['citName' => $request->get('city')['citName'],
                    'citPostCode' => $request->get('city')['citPostCode']]
                );

                if ($foundCity === null) {
                    $entityManager->persist($city);
                    $entityManager->flush();

                    $location->setLocCity($city);
                } else {
                    $location->setLocCity($foundCity);
                }

                $entityManager->persist($location);
                $entityManager->flush();

                $trip->setTriLocation($location);
            }

            $trip->setTriState($state);
            $trip->setTriOrganiser($participant);
            $entityManager->persist($trip);
            $entityManager->flush();

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('trip/new.html.twig', [
            "tripForm" => $tripForm,
            "locationForm" => $locationForm,
            "cityForm" => $cityForm,
        ]);
    }

    #[Route('/search-location/{id}', name:'_searchLocation')]
    public function searchLocation(Request $request, LocationRepository $locationRepository): Response {

        $result = $locationRepository->findBy([
            'locCity' => $request->get('id'),
        ]);

        $locations = [];
        foreach ($result as $location) {
            $locations[] = ['id' => $location->getId(), 'name' => $location->getLocName()];
        }

        return $this->json(sprintf('{"results":%s, "code":%b, "type":"locations"}', json_encode($locations), true));
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

        if (
            $trip->getTriState()->getStaLabel() !== State::STATE_OPEN
            && $trip->getTriState()->getStaLabel() !== State::STATE_CREATED
            && $trip->getTriState()->getStaLabel() !== State::STATE_CLOSED_SUBSCRIBE
        ) {
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

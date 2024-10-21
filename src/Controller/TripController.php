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
use App\Service\CityService;
use App\Service\LocationService;
use App\Service\StateService;
use App\Service\TripService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/trip', name: 'app_trip')]
class TripController extends AbstractController
{
    public function __construct(TripService $tripService,CityService $cityService,LocationService $locationService,StateService $stateService){
        $this->tripService = $tripService;
        $this->cityService = $cityService;
        $this->locationService = $locationService;
        $this->stateService = $stateService;
    }
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
        //Vérification de l'état
       $state = $this->stateService->getStateForm($request);

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
            if (!$this->tripService->checkDateTripForm($trip)) {
                $this->addFlash('danger', 'La date de clôture ne peut pas être supérieur à la date de sortie.');
                return $this->render('trip/new.html.twig', [
                    "tripForm" => $tripForm->createView(),
                    "locationForm" => $locationForm->createView(),
                    "cityForm" => $cityForm->createView(),
                ]);
            }
            //Vérifier s'il y a des données
            // Cas : l'utilisateur ajoute un lieu et une ville dans le formulaire
            if (!empty($request->get('location')['locName']) || !empty($request->get('location')['locStreet']) ||
                !empty($request->get('location')['locLatitude']) || !empty($request->get('location')['locLongitude']) ||
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

            $this->addFlash('success', 'Votre sortie a bien été créée');
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('trip/new.html.twig', [
            "tripForm" => $tripForm,
            "locationForm" => $locationForm,
            "cityForm" => $cityForm,
        ]);
    }
    #[Route('/update/{id}', name: '_update_get', methods: ['GET'])]
    function updateTrip(Trip $trip,#[CurrentUser] ?Participant $participant): Response {
        if(!$this->tripService->checkUpdate($trip,$participant)){
            $this->addFlash('danger','Vous ne pouvez pas modifier cette sortie');
            return $this->redirectToRoute('app_home');
        }

        $location = $trip->getTriLocation();
        $city = $location->getLocCity();
        $tripForm = $this->createForm(TripType::class, $trip);
        $locationForm = $this->createForm(LocationType::class, $location);
        $cityForm = $this->createForm(CityType::class, $city);

        return $this->render('trip/update.html.twig', [
            "tripForm" => $tripForm,
            "locationForm" => $locationForm,
            "cityForm" => $cityForm,
        ]);
    }

    #[Route('/update/{id}', name: 'app_trip_update_post', methods: ['POST'])]
    public function updateTripPost(
        Trip $trip,
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?Participant $participant
    ): Response {
        // Créer et gérer le formulaire du voyage
        $tripForm = $this->createForm(TripType::class, $trip);
        $tripForm->handleRequest($request);

        // Créer une nouvelle instance de Location et gérer le formulaire de localisation
        $location = new Location();
        $locationForm = $this->createForm(LocationType::class, $location);
        $locationForm->handleRequest($request);

        // Créer une nouvelle instance de City et gérer le formulaire de ville
        $city = new City();
        $cityForm = $this->createForm(CityType::class, $city);
        $cityForm->handleRequest($request);

        // Vérifier si le formulaire du voyage a été soumis et est valide
        if ($tripForm->isSubmitted() && $tripForm->isValid()) {
            // Récupérer les données de localisation
            $locationData = $locationForm->getData();

            // Vérifiez si la localisation a changé
            if ($this->locationService->isNewLocationDifferent(
                $locationData->getLocName(),
                $locationData->getLocStreet(),
                $locationData->getLocCity(),
                $locationData->getLocLatitude(),
                $locationData->getLocLongitude(),
                $trip->getTriLocation()
            )) {
                // Créer et persister la nouvelle localisation
                $entityManager->persist($locationData);
                $trip->setTriLocation($locationData);
            } else {
                // Si pas de changement, garder l'ancienne localisation
                $trip->setTriLocation($trip->getTriLocation());
            }

            // Mettre à jour l'état et l'organisateur du voyage
            $trip->setTriState($this->stateService->getStateForm($request));
            $trip->setTriOrganiser($participant);

            // Persist the updated trip
            $entityManager->persist($trip);
            $entityManager->flush();

            return $this->redirectWithFlash('success', 'Votre sortie a bien été mise à jour', 'app_home');
        }

        // Rendu du formulaire avec les vues
        return $this->renderTripForm($tripForm, $locationForm, $cityForm);
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
            $state = $stateRepository->findOneBy(['staLabel' => State::STATE_CANCELLED]);
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

    /**
     * Redirige avec un message flash.
     */
    private function redirectWithFlash(string $type, string $message, string $route, array $params = []): Response {
        $this->addFlash($type, $message);
        return $this->redirectToRoute($route, $params);
    }

    /**
     * Rend le formulaire de voyage avec les formulaires de localisation et de ville.
     */
    private function renderTripForm($tripForm, $handledLocation): Response {
        return $this->render('trip/new.html.twig', [
            "tripForm" => $tripForm->createView(),
            "locationForm" => $this->createForm(LocationType::class, $handledLocation)->createView(),
            "cityForm" => $this->createForm(CityType::class, $handledLocation->getLocCity())->createView(),
        ]);
    }



}

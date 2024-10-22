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
    public function __construct(TripService $tripService, CityService $cityService, LocationService $locationService, StateService $stateService){
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

    /**
     * @Route("/new", name="_post", methods={"POST"})
     *
     * Cette méthode permet de traiter la création d'un nouveau voyage (Trip).
     * Elle gère les soumissions de formulaires pour les entités Trip, Location, et City,
     * et délègue les opérations spécifiques à des services dédiés pour assurer une meilleure séparation des responsabilités.
     *
     * Fonctionnalités principales :
     * - Valide les données soumises dans les formulaires (Location, City, Trip).
     * - Vérifie la cohérence des dates du voyage (date de début et date de clôture).
     * - Si les champs relatifs à la ville (City) sont renseignés, elle vérifie si la ville existe déjà ou la crée.
     * - Persiste les données de la localisation et du voyage.
     * - Utilise des services pour décomposer la logique métier, rendant le code plus maintenable.
     *
     * @param Request $request - Requête contenant les données du formulaire soumises par l'utilisateur.
     * @param EntityManagerInterface $entityManager - Permet de gérer les opérations de persistance dans la base de données.
     * @param Participant $participant - Utilisateur courant, récupéré automatiquement via l'annotation #[CurrentUser].
     *
     * @return Response - Renvoie une réponse HTTP, soit redirigeant vers une page après succès, soit réaffichant le formulaire en cas d'erreur.
     *
     * Flux de contrôle :
     * 1. Récupération de l'état sélectionné dans le formulaire via `stateService`.
     * 2. Gestion des formulaires de localisation et de ville.
     * 3. Validation des données du formulaire de voyage.
     * 4. Vérification de la validité des dates de voyage via `tripService->checkDateTripForm()`.
     * 5. Gestion de la création de la ville si les champs sont remplis via `cityService`.
     * 6. Persistance des entités Trip, Location, et City.
     * 7. Redirection vers la page d'accueil avec un message de succès après persistance.
     */
    #[Route('/new', name: '_post', methods: ['POST'])]
    public function post(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] Participant $participant
    ): Response {
        // Vérification de l'état
        $state = $this->stateService->getStateForm($request);

        // Formulaire de location
        $location = new Location();
        $locationForm = $this->createForm(LocationType::class, $location);
        $locationForm->handleRequest($request);

        // Formulaire de ville
        $cityForm = $this->createForm(CityType::class, new City());
        $cityForm->handleRequest($request);

        // Formulaire de voyage
        $trip = new Trip();
        $tripForm = $this->createForm(TripType::class, $trip);
        $tripForm->handleRequest($request);

        if(!$this->tripService->checkDateTripForm($trip)){
            $this->addFlash('danger','La date de clôture doit se terminer avant la date de début');
            return $this->redirectToRoute('app_trip_update_get',['id'=>$trip->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($tripForm->isSubmitted() && $tripForm->isValid()) {
            // Récupérer les données de la ville
            $cityData = $request->get('city');

            // Vérifier si les données de la ville sont valides et si on a entré une nouvelle localisation
            if ($this->cityService->isCityDataValid($cityData)) {
                // Si les données sont valides, gérer la création de la ville
             $this->cityService->handleCityCreation($cityData, $location,$trip);
            }
            // Créer et persister le voyage
            $this->tripService->createTrip($trip,$state,$participant);
            $this->addFlash('success', 'Votre sortie a été créée avec succès');
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('trip/new.html.twig', [
            "tripForm" => $tripForm,
            "locationForm" => $locationForm,
            "cityForm" => $cityForm,
        ]);
    }


    /**
     * @Route("/update/{id}", name="_update_get", methods={"GET"})
     *
     * Cette méthode permet d'afficher le formulaire de mise à jour d'une sortie (Trip) existante.
     * Avant d'afficher les formulaires, elle vérifie si l'utilisateur connecté a les droits pour modifier la sortie via un service.
     *
     * Fonctionnalités principales :
     * - Vérifie si l'utilisateur connecté a l'autorisation de modifier la sortie.
     * - Récupère les données existantes pour le voyage (Trip), la localisation (Location) et la ville (City).
     * - Crée les formulaires associés (Trip, Location, City) pré-remplis avec les données actuelles.
     *
     * @param Trip $trip - L'entité Trip à mettre à jour, récupérée via l'ID dans l'URL.
     * @param Participant|null $participant - L'utilisateur actuellement connecté (ou null s'il n'est pas authentifié).
     *
     * @return Response - Affiche le formulaire de mise à jour ou redirige vers la page d'accueil avec un message d'erreur.
     *
     * Flux de contrôle :
     * 1. Vérifie si l'utilisateur peut modifier la sortie via `tripService->checkUpdate()`.
     * 2. Récupère les données actuelles du voyage, de la localisation et de la ville.
     * 3. Génère les formulaires pré-remplis avec les données actuelles.
     * 4. Si l'utilisateur n'a pas les droits, affiche un message d'erreur et redirige vers la page d'accueil.
     */
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

    /**
     * @Route("/update/{id}", name="_update_post", methods={"POST"})
     *
     * Cette méthode traite la soumission du formulaire de mise à jour d'une sortie (Trip) existante.
     * Elle vérifie la validité des données soumises et met à jour le voyage, la localisation et la ville si nécessaire.
     *
     * Fonctionnalités principales :
     * - Récupère et valide l'état du voyage à partir du formulaire.
     * - Crée des formulaires pré-remplis pour le voyage, la localisation et la ville.
     * - Vérifie que la date de clôture est antérieure à la date de début du voyage.
     * - Gère les changements de ville et de localisation via des services dédiés.
     * - Met à jour et persiste les modifications du voyage dans la base de données.
     *
     * @param Trip $trip - L'entité Trip à mettre à jour, récupérée via l'ID dans l'URL.
     * @param Request $request - La requête HTTP contenant les données du formulaire.
     * @param Participant $participant - L'utilisateur actuellement connecté, utilisé pour vérifier les autorisations.
     *
     * @return Response - Redirige vers la page d'accueil après succès ou réaffiche le formulaire en cas d'erreur.
     *
     * Flux de contrôle :
     * 1. Récupère l'état du voyage à partir du formulaire.
     * 2. Crée des formulaires pré-remplis pour le voyage, la localisation et la ville.
     * 3. Gère la soumission des formulaires et la validation des données.
     * 4. Vérifie si les dates sont valides via `tripService->checkDateTripForm()`.
     * 5. Si les données de la ville ou de la localisation changent, gère les modifications via les services dédiés.
     * 6. Met à jour le voyage via `tripService->updateTrip()`.
     */
    #[Route('/update/{id}', name: '_update_post', methods: ['POST'])]
    public function update(Trip $trip, Request $request, #[CurrentUser] Participant $participant): Response
    {
        $state = $this->stateService->getStateForm($request);

        $location = $trip->getTriLocation();
        $city = $location->getLocCity();

        $tripForm = $this->createForm(TripType::class, $trip);
        $locationForm = $this->createForm(LocationType::class, $location);
        $cityForm = $this->createForm(CityType::class, $city);

        $tripForm->handleRequest($request);
        $locationForm->handleRequest($request);
        $cityForm->handleRequest($request);

        if(!$this->tripService->checkDateTripForm($trip)){
            $this->addFlash('danger','La date de clôture doit se terminer avant la date de début');
            return $this->redirectToRoute('app_trip_update_get',['id'=>$trip->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($tripForm->isSubmitted() && $tripForm->isValid()) {
            // Gérer les changements de ville
            $newCity = $tripForm->get('triLocation')->getData()->getLocCity();
            $updatedCity = $this->cityService->handleCityChange($city, $newCity);

            // Gérer les changements de localisation
            $newLocation = $tripForm->get('triLocation')->getData();
            $updatedLocation = $this->locationService->handleLocationChange($location, $newLocation);

            // Mettre à jour le Trip
            $this->tripService->updateTrip($trip, $updatedLocation, $state, $participant);

            $this->addFlash('success', 'Votre sortie a bien été mise à jour');
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('trip/update.html.twig', [
            'tripForm' => $tripForm->createView(),
            'locationForm' => $locationForm->createView(),
            'cityForm' => $cityForm->createView(),
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
}

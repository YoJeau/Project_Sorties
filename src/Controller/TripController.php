<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Location;
use App\Entity\Participant;
use App\Entity\Trip;
use App\Form\CityType;
use App\Form\LocationType;
use App\Form\TripType;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use App\Repository\StateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/trip')]
class TripController extends AbstractController
{
    #[Route('/trip', name: 'app_trip')]
    public function index(): Response
    {
        return $this->render('trip/index.html.twig', [
            'controller_name' => 'TripController',
        ]);
    }

    #[Route('/new', name: 'app_trip_new', methods: ['GET'])]
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

    #[Route('/search-location/{id}', name:'app_trip_searchLocation')]
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

    #[Route('/new', name: 'app_trip_post', methods: ['POST'])]
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
            $trip->setTriOrganiser($this->getUser());
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
}

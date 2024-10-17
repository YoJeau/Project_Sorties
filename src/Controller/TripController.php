<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Location;
use App\Entity\State;
use App\Entity\Trip;
use App\Form\CityType;
use App\Form\LocationType;
use App\Form\TripType;
use App\Repository\StateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

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

    #[Route('/new', name: 'app_trip_post', methods: ['POST'])]
    public function post(Request $request, EntityManagerInterface $entityManager, StateRepository $stateRepository) : Response {

        $test = $request->request;
        dd(new \DateTimeImmutable());
        dd(isset($request->get('trip')['state']));
//        dd($request->query->get('trip'));

//        if ()

        $trip = new Trip();
        $tripForm = $this->createForm(TripType::class, $trip);
        $tripForm->handleRequest($request);

        dd($tripForm->getData());

//        $location = new Location();
//        $locationForm = $this->createForm(LocationType::class, $location);
//        $locationForm->handleRequest($request);
//
//        $city = new City();
//        $cityForm = $this->createForm(CityType::class, $city);
//        $cityForm->handleRequest($request);


        if ($tripForm->isSubmitted() && $tripForm->isValid()) {
            $entityManager->persist($trip);
            $entityManager->flush();
        }

//        if ($locationForm->isSubmitted() && $locationForm->isValid()) {
//            $entityManager->persist($location);
//            $entityManager->flush();
//        }
//
//        if ($cityForm->isSubmitted() && $cityForm->isValid()) {
//            $entityManager->persist($city);
//            $entityManager->flush();
//        }

        return $this->redirectToRoute('app_trip_new', [], Response::HTTP_SEE_OTHER);
    }
}

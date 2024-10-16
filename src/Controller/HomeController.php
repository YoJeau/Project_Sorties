<?php

namespace App\Controller;

use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Repository\TripRepository;
use App\Service\ActionService;
use App\Service\FilterService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class HomeController extends AbstractController
{
    public function __construct(
        SiteRepository $siteRepository,
        TripRepository $tripRepository,
        ActionService $actionService
    ){
        $this->siteRepository = $siteRepository;
        $this->tripRepository = $tripRepository;
        $this->actionService = $actionService;
    }
    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(Request $request, PaginatorInterface $paginator, FilterService $filterService): Response
    {
        if (!$this->getUser()) return $this->redirectToRoute('app_login');

        $sites = $this->siteRepository->findAll();
        $filters = [];
        if ($request->isMethod('POST')) {
            $filters = [
                'site' => $request->request->get('name-site'),
                'searchName' => $request->request->get('search-name-site'),
                'startDate' => $request->request->get('start-date'),
                'endDate' => $request->request->get('end-date'),
                'organisatorTrip' => $request->request->get('organisator-trip') === 'yes',
                'subcribeTrip' => $request->request->get('subcribed-trip') === 'yes',
                'notSubcribeTrip' => $request->request->get('not-subscribed-trip') === 'yes',
                'ancientTrip' => $request->request->get('ancient-trip') === 'yes',
            ];
            $user = $this->getUser();

            $filteredTrips = $filterService->filterTrips($filters, $user);

            $trips = $paginator->paginate($filteredTrips, $request->query->getInt('page', 1), 5);
        } else {
            $trips = $paginator->paginate($this->tripRepository->findAll(), $request->query->getInt('page', 1), 5);
        }

        $actions = $this->actionService->determineAction($this->getUser(), $trips);

        return $this->render('home/index.html.twig', [
            'sites' => $sites,
            'trips' => $trips,
            'actions' => $actions,
            'filters' => $filters,
        ]);
    }
}

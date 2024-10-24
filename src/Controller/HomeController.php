<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\State;
use App\Repository\SiteRepository;
use App\Repository\TripRepository;
use App\Service\ActionService;
use App\Service\StateService;
use App\Service\FilterService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly SiteRepository $siteRepository,
        private readonly TripRepository $tripRepository,
        private readonly ActionService $actionService,
        private readonly StateService $checkStateService,
        private readonly FilterService $filterService
    )
    { }

    /**
     * @throws \DateMalformedStringException
     */
    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(#[CurrentUser] ?Participant $currentParticipant, Request $request, PaginatorInterface $paginator): Response
    {
        if (is_null($currentParticipant)) return $this->redirectToRoute('app_login');

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

            $filteredTrips = $this->filterService->filterTrips($filters, $user);

            $trips = $paginator->paginate($filteredTrips, $request->query->getInt('page', 1), 10);
        } else {
            $this->checkStateService->checkState();
            $trips = $this->tripRepository->findNonArchivedTrips();
            $trips = $paginator->paginate($trips, $request->query->getInt('page', 1), 10);
        }
        $actions = $this->actionService->determineAction($currentParticipant, $trips);
        $stateColors = [
            State::STATE_COMPLETED => "table-dark",
            State::STATE_OPEN => "table-light",
            State::STATE_CREATED => "table-primary",
            State::STATE_CLOSED_SUBSCRIBE => "table-warning",
            State::STATE_CANCELLED => "table-danger",
            State::STATE_CLOSED => "table-secondary",
            State::STATE_IN_PROGRESS => "table-success",
            State::STATE_ARCHIVED => "table-dark"
        ];
        return $this->render('home/index.html.twig', [
            'sites' => $sites,
            'trips' => $trips,
            'actions' => $actions,
            'filters' => $filters,
            'stateColors' => $stateColors,
        ]);
    }
}

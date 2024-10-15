<?php

namespace App\Controller;

use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Repository\TripRepository;
use App\Service\ActionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        if(!$this->getUser()) return $this->redirectToRoute('app_login');
        $sites = $this->siteRepository->findAll();
        $trips = $this->tripRepository->findAll();
        $this->actionService->determineAction($this->getUser(), $trips);
        return $this->render('home/index.html.twig', [
            'sites' => $sites,
            'trips' => $trips,
        ]);
    }
}

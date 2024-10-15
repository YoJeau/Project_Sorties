<?php

namespace App\Controller;

use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Repository\TripRepository;
use App\Service\ActionService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(Request $request,PaginatorInterface $paginator ): Response
    {
        if(!$this->getUser()) return $this->redirectToRoute('app_login');
        $sites = $this->siteRepository->findAll();
        $trips = $paginator->paginate($this->tripRepository->findAll(),$request->query->getInt('page',1),5);
        $actions = $this->actionService->determineAction($this->getUser(), $trips);
        return $this->render('home/index.html.twig', [
            'sites' => $sites,
            'trips' => $trips,
            'actions' => $actions,
        ]);
    }
}

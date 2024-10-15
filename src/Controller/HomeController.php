<?php

namespace App\Controller;

use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Repository\TripRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(SiteRepository $siteRepository,TripRepository $tripRepository){
        $this->siteRepository = $siteRepository;
        $this->tripRepository = $tripRepository;
    }
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $sites = $this->siteRepository->findAll();
        $trips = $this->tripRepository->findAll();
        return $this->render('home/index.html.twig', [
            'sites' => $sites,
            'trips' => $trips,
        ]);
    }
}

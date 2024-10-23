<?php

namespace App\Controller;

use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/city',name:'app_city_')]
class CityController extends AbstractController
{
    public function __construct(CityRepository $cityRepository){
        $this->cityRepository = $cityRepository;
    }
    #[Route('/', name: 'index')]
    public function index(){

        $cities = $this->cityRepository->findAll();
        return $this->render('city/index.html.twig', [
            'cities' => $cities,
        ]);
    }
}
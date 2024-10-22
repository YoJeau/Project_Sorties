<?php

namespace App\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use App\Service\CityService;
use App\Service\LocationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/city',name:'app_city_')]
class CityController extends AbstractController
{
    public function __construct(CityRepository $cityRepository,CityService $cityService,EntityManagerInterface $entityManager,LocationService $locationService){
        $this->cityRepository = $cityRepository;
        $this->cityService = $cityService;
        $this->entityManager = $entityManager;
        $this->locationService = $locationService;
    }
    #[Route('/', name: 'index')]
    public function index(){

        $cities = $this->cityRepository->findAll();
        return $this->render('city/index.html.twig', [
            'cities' => $cities,
        ]);
    }
    #[Route('/create', name: 'create')]
    public function addCity(Request $request){
        $data = json_decode($request->getContent(), true);
        $cityId = $this->cityService->addCity($data);
        if($cityId) {
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Ville ajoutée avec succès.',
                'cityId' => $cityId, // Renvoyer l'ID de la ville ajoutée
            ]);
        } else{
            return new JsonResponse(['status' => 'error', 'message' => 'Cette ville existe deja'], 400);
        }
    }

    #[Route('/update/{id}', name: 'update',methods: ['POST'])]
    public function updateCity(Request $request,City $city):JsonResponse{
        $data = json_decode($request->getContent(),true);
        if(!$this->cityService->updateCity($city,$data)) return new JsonResponse(['status' => 'error', 'message' => 'Données invalides.'], 400);
        return new JsonResponse(['status' => 'success', 'message' => 'Ville mise à jour avec succès.'], 200);
    }

    #[Route('/delete/{id}', name: 'delete',methods: ['POST'])]
    public function deleteCity(City $city):JsonResponse{
        if(!$this->locationService->isCityLinkedToLocation($city)) return new JsonResponse(['status' => 'error', 'message' => 'Cette vile est rattaché a une location'], 400);
        if(!$this->cityService->deleteCity($city)) return new JsonResponse(['status' => 'error', 'message' => 'Erreur lors de la suppression'], 400);
        return new JsonResponse(['status' => 'success', 'message' => 'Ville supprimée avec succès.'], 200);
    }
}
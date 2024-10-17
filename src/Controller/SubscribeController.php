<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Service\SubcribeService;
use App\Service\SubscribeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class SubscribeController extends AbstractController
{
    public function __construct(SubscribeService $subcribeService){
        $this->subcribeService = $subcribeService;
    }

    #[Route('/subscribe/{id}', name: 'app_subscribe')]
    public function subcribe(Trip $trip,Request $request){
        $user = $this->getUser();
       if($this->subcribeService->checkSubscribe($trip,$user)){
           $this->addFlash('success', 'Vous êtes inscrit avec succès ');
       } else{
           $this->addFlash('danger','les inscriptions ne sont plus disponibles');
       }
       return $this->redirectToRoute('app_home');
    }

    #[Route('/unsubscribe/{id}', name: 'app_subscribe_delete')]
    public function unsubscribe(Trip $trip,Request $request){
        $user = $this->getUser();
        if($this->subcribeService->checkUnsubscribe($trip,$user)){
            $this->addFlash('success','Vous vous êtes désinscrit avec succès');
        } else{
            $this->addFlash('danger','Les desistements ne sont plus disponibles');
        }
        return $this->redirectToRoute('app_home');
    }
}
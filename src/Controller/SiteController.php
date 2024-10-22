<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SiteController extends AbstractController
{
    /**
     * Afficher et ajouter un site
     * @param SiteRepository $siteRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/site', name:'app_site')]
    public function index(SiteRepository $siteRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $site = new Site();
        $siteForm = $this->createForm(SiteType::class, $site);
        $siteForm->handleRequest($request);

        //Ajouter un site
        if ($siteForm->isSubmitted() && $siteForm->isValid()) {
            $entityManager->persist($site);
            $entityManager->flush();

            return $this->redirectToRoute('app_site');
        }

        return $this->render('site/index.html.twig', [
            'sites' => $siteRepository->findAll(),
            'siteForm' => $siteForm,
        ]);
    }

    #[Route('/site/edit/{id}', name:'app_site_edit')]
    public function edit(Site $site, Request $request, EntityManagerInterface $entityManager) : Response {
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $entityManager->flush();
        }


        return $this->redirectToRoute('app_site');
    }

    /**
     * Suppression d'un site
     * @param Request $request
     * @param Site $site
     * @param EntityManagerInterface $entityManager
     * @param ParticipantRepository $participantRepository
     * @return Response
     */
    #[Route('/site/{id}', name: 'app_site_delete', methods: ['POST'])]
    public function delete(Request $request, Site $site, EntityManagerInterface $entityManager, ParticipantRepository $participantRepository): Response {

        //Vérification si un site est rattaché à un participant
        $foundSite = $participantRepository->findBy(['parSite' => $site->getId()]);

        if (count($foundSite) > 0) {
            $this->addFlash('danger', "Suppression impossible : des participants sont rattachés à ce site.");
            return $this->redirectToRoute('app_site');
        }

        if (!$this->isCsrfTokenValid('delete'.$site->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Invalid CSRF token');
            return $this->redirectToRoute('app_site');
        }
        $entityManager->remove($site);
        $entityManager->flush();

        return $this->redirectToRoute('app_site');
    }
}

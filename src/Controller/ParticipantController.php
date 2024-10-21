<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Service\ImageManagerService;
use App\Service\PasswordManagerService;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/participant', name: 'app_participant')]
class ParticipantController extends AbstractController
{
    private ImageManagerService $imageManagerService;
    private PasswordManagerService $passwordManagerService;

    public function __construct(ImageManagerService $imageManagerService, PasswordManagerService $passwordManagerService){
        $this->imageManagerService = $imageManagerService;
        $this->passwordManagerService = $passwordManagerService;
    }

    /**
     * Displays the participant administration panel,
     * with a list of participants and possible actions.
     *
     * @param ParticipantRepository $participantRepository
     * @return Response
     */
    #[Route('/administration', name: '_administration', methods: ['GET'])]
    public function administration(ParticipantRepository $participantRepository): Response
    {
        $participants = $participantRepository->findAll();
        return $this->render('participant/index.html.twig', [
            'participants' => $participants
        ]);
    }

    /**
     * GET - Displays the profile of the logged-in participant with an edit form.
     * POST - Saves changes made to the profile, then returns to this profile.
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Participant|null $participant
     * @return Response
     */
    #[Route('/my-profile', name: '_my-profile', methods: ['GET', 'POST'])]
    public function myProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?Participant $participant
    ): Response
    {
        $picture = 'default.png';

        if ($participant->getParPicture()) {
            $picture = $participant->getParPicture();
        }

        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        // if the form has been submitted and is valid,
        // proceed to save changes before redirecting to the profile display.
        if ($form->isSubmitted() && $form->isValid()) {
            $pictureFile = $form->get('parPicture')->getData();

            // if an image has been added to the form, start image management
            if (!empty($pictureFile)) {
                try {
                    $this->imageManagerService->manageImage($participant, $pictureFile);
                } catch (FileException $e) {
                    $this->addFlash('danger', "Erreur lors du téléchargement de l'image !");

                    return $this->redirectToRoute('app_participant_my-profile');
                }
            }

            // if a new password has been added to the form, start password management
            if (!empty($form->get('plainPassword')->getData())) {
                $currentPassword = $form->get('currentPassword')->getData();
                $plainPassword = $form->get('plainPassword')->getData();
                $confirmPassword = $form->get('confirmPassword')->getData();
                try {
                    $this->passwordManagerService->managePassword($participant, $currentPassword, $plainPassword, $confirmPassword);
                } catch (\RuntimeException $e) {
                    $this->addFlash('danger', $e->getMessage());

                    return $this->redirectToRoute('app_participant_my-profile');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a bien été mis à jour.');

            return $this->redirectToRoute('app_participant_my-profile');
        }
        return $this->render('participant/my-profile.html.twig', [
            'picture' => $picture,
            'form' => $form->createView()
        ]);
    }

    /**
     * Displays a participant's profile.
     *
     * @param Participant|null $currentParticipant
     * @param Participant $participant
     * @return Response
     */
    #[Route('/show/{id}', name: '_show', methods: ['GET'])]
    public function show(#[CurrentUser] ?Participant $currentParticipant, Participant $participant): Response
    {
        if ($currentParticipant && $currentParticipant->getId() === $participant->getId()) {
            return $this->redirectToRoute('app_participant_my-profile');
        }

        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    /**
     * Create a new participant.
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $passwordHasher
     * @return Response
     */
    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $participant = new Participant();

        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participant->setPassword($passwordHasher->hashPassword($participant, 'bonjour-ENI-123'));
            $participant->setRoles(['ROLE_USER']);
            $participant->setParIsActive(true);

            $entityManager->persist($participant);
            $entityManager->flush();


            return $this->redirectToRoute('app_participant_administration');
        }

        return $this->render('participant/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/import', name: '_import', methods: ['GET', 'POST'])]
    public function import(
    ): Response
    {
        $csv = Reader::createFromPath('participants.csv', 'r');
        $csv->setHeaderOffset(0);

        $header = $csv->getHeader();

        $records = $csv->getRecords();

//        dd([$records, $header]);

//        $form = $this->createForm(ParticipantType::class, $participant);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $participant->setPassword($passwordHasher->hashPassword($participant, 'bonjour-ENI-123'));
//            $participant->setRoles(['ROLE_USER']);
//            $participant->setParIsActive(true);
//
//            $entityManager->persist($participant);
//            $entityManager->flush();
//
//
//            return $this->redirectToRoute('app_participant_administration');
//        }
//
//        return $this->render('participant/new.html.twig', [
//            'form' => $form
//        ]);
        return $this->render('participant/import.html.twig', []);
    }
}

<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Service\ImageManagerService;
use App\Service\PasswordManagerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    #[Route('/{id}', name: '_show', methods: ['GET'])]
    public function show(#[CurrentUser] ?Participant $currentParticipant, Participant $participant): Response
    {
        if ($currentParticipant && $currentParticipant->getId() === $participant->getId()) {
            return $this->redirectToRoute('app_participant_my-profile');
        }

        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }
}

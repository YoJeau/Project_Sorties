<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/participant', name: 'app_participant')]
class ParticipantController extends AbstractController
{
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
        UserPasswordHasherInterface $passwordHasher,
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
                $fileName = md5(uniqid()) . '.' . $pictureFile->guessExtension();

                // saves the image in the target folder
                try {
                    $pictureFile->move(
                        $this->getParameter('upload_path'),
                        $fileName
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', "Erreur lors du téléchargement de l'image !");

                    return $this->redirectToRoute('app_participant_my-profile');
                }

                // if the participant has an existing image, delete it from the server
                if (!empty($participant->getParPicture())) {
                    $oldFileName = $participant->getParPicture();
                    $oldFilePath = $this->getParameter('upload_path') . '/' . $oldFileName;

                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
                // adds new image name to participant
                $participant->setParPicture($fileName);
            }

            // if a new password has been added to the form, start password management
            if (!empty($form->get('plainPassword')->getData())) {
                $currentPassword = $form->get('currentPassword')->getData();

                // if the current password does not match, returns a flash message
                if ($currentPassword && !$passwordHasher->isPasswordValid($participant, $currentPassword)) {
                    $this->addFlash('danger', "Mot de passe incorrect");

                    return $this->redirectToRoute('app_participant_my-profile');
                }

                $plainPassword = $form->get('plainPassword')->getData();
                $confirmPassword = $form->get('confirmPassword')->getData();

                // if the new password does not match the confirmation, returns a flash message
                if (empty($confirmPassword) || $confirmPassword !== $plainPassword) {
                    $this->addFlash('danger', "Les mots de passe ne correspondent pas");

                    return $this->redirectToRoute('app_participant_my-profile');
                }

                $participant->setPassword(
                    $passwordHasher->hashPassword(
                        $participant,
                        $plainPassword
                    )
                );
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

    #[Route('/{id}', name: '_show', methods: ['GET'])]
    public function show(Participant $participant): Response
    {
        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }
}

<?php

namespace App\Service;

use App\Entity\Participant;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageManagerService
{
    private $uploadPath;

    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadPath = $params->get('upload_path');
    }

    public function manageImage(Participant $participant, ?UploadedFile $pictureFile): void
    {
        if ($pictureFile) {
            $fileName = md5(uniqid()) . '.' . $pictureFile->guessExtension();

            try {
                $pictureFile->move($this->uploadPath, $fileName);
            } catch (FileException $e) {
                throw new \RuntimeException("Erreur lors du téléchargement de l'image !");
            }

            if (!empty($participant->getParPicture())) {
                $oldFilePath = $this->uploadPath . '/' . $participant->getParPicture();
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            $participant->setParPicture($fileName);
        }
    }
}
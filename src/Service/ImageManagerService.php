<?php

namespace App\Service;

use App\Entity\Participant;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageManagerService
{
    private string $uploadPath;

    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadPath = $params->get('upload_path');
    }

    public function manageImage(Participant $participant, ?UploadedFile $pictureFile): void
    {
        $fileName = md5(uniqid()) . '.' . $pictureFile->guessExtension();

        // saves the image in the target folder
        $pictureFile->move($this->uploadPath, $fileName);

        // if the participant has an existing image, delete it from the server
        if (!empty($participant->getParPicture())) {
            $oldFileName = $participant->getParPicture();
            $oldFilePath = $this->uploadPath . '/' . $oldFileName;

            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }
        $participant->setParPicture($fileName);
    }
}
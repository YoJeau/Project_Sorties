<?php

namespace App\Service;

use App\Entity\Participant;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\UnavailableStream;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantService
{
    public function __construct(
        private readonly CSVService $CSVService,
        private readonly SiteRepository $siteRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    /**
     * Gets the list of participants from a CSV file.
     *
     * @param string $filePath path to CSV file
     * @return void
     * @throws Exception
     * @throws UnavailableStream
     */
    public function getParticipantsFromCSV(string $filePath): void
    {
        $records = $this->CSVService->getRecords($filePath);

        if (empty($records)) {
            return ;
        }

        $participants = [];
        $password = $this->parameterBag->get('default_pwd');
        foreach ($records as $offset => $record) {
            $site = $this->siteRepository->findOneBy(['sitName' => $record['site']]);
            $participants[$offset] = new Participant();;
            $participants[$offset]->setParUsername($record['firstname'] . '_' . $record['lastname']);
            $participants[$offset]->setRoles(['ROLE_USER']);
            $participants[$offset]->setPassword($this->passwordHasher->hashPassword($participants[$offset], $password));
            $participants[$offset]->setParLastName($record['lastname']);
            $participants[$offset]->setParFirstName($record['firstname']);
            $participants[$offset]->setParPhone($record['phone']);
            $participants[$offset]->setParEmail($record['email']);
            $participants[$offset]->setParSite($site);
            $this->entityManager->persist($participants[$offset]);
        }

        $this->entityManager->flush();
    }
}

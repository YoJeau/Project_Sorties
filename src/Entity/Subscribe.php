<?php

namespace App\Entity;

use App\Repository\SubscribeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SubscribeRepository::class)]
class Subscribe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['subscribe:read'])]
    private ?int $subId = null;

    #[ORM\ManyToOne(inversedBy: 'subTripId')]
    #[ORM\JoinColumn(name: 'sub_participant_id', referencedColumnName: 'par_id', nullable: false)]
    #[Groups(['subscribe:read'])]
    private ?Participant $subParticipantId = null;

    #[ORM\ManyToOne(inversedBy: 'subscribes')]
    #[ORM\JoinColumn(name: 'sub_trip_id', referencedColumnName: 'tri_id', nullable: false)]
    #[Groups(['subscribe:read'])]
    private ?Trip $subTripId = null;

    public function getId(): ?int
    {
        return $this->subId;
    }

    public function getSubParticipantId(): ?Participant
    {
        return $this->subParticipantId;
    }

    public function setSubParticipantId(?Participant $subParticipantId): static
    {
        $this->subParticipantId = $subParticipantId;

        return $this;
    }

    public function getSubTripId(): ?Trip
    {
        return $this->subTripId;
    }

    public function setSubTripId(?Trip $subTripId): static
    {
        $this->subTripId = $subTripId;

        return $this;
    }
}

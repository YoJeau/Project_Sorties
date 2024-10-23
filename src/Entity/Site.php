<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
class Site
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $sitId = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner le nom de la sortie.')]
    #[Assert\Length(max: 30, maxMessage: "Maximum {{ limit }} caractères.")]
    #[ORM\Column(length: 30)]
    private ?string $sitName = null;

    /**
     * @var Collection<int, Trip>
     */
    #[ORM\OneToMany(targetEntity: Trip::class, mappedBy: 'triSite')]
    private Collection $sitTrips;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\OneToMany(targetEntity: Participant::class, mappedBy: 'parSite')]
    private Collection $sitParticipants;

    public function __construct()
    {
        $this->sitTrips = new ArrayCollection();
        $this->sitParticipants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->sitId;
    }

    public function getSitName(): ?string
    {
        return $this->sitName;
    }

    public function setSitName(string $sitName): static
    {
        $this->sitName = $sitName;

        return $this;
    }

    /**
     * @return Collection<int, Trip>
     */
    public function getSitTrips(): Collection
    {
        return $this->sitTrips;
    }

    public function addTrip(Trip $trip): static
    {
        if (!$this->sitTrips->contains($trip)) {
            $this->sitTrips->add($trip);
            $trip->setTriSite($this);
        }

        return $this;
    }

    public function removeTrip(Trip $trip): static
    {
        if ($this->sitTrips->removeElement($trip)) {
            // set the owning side to null (unless already changed)
            if ($trip->getTriSite() === $this) {
                $trip->setTriSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getSitParticipants(): Collection
    {
        return $this->sitParticipants;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->sitParticipants->contains($participant)) {
            $this->sitParticipants->add($participant);
            $participant->setParSite($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): static
    {
        if ($this->sitParticipants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getParSite() === $this) {
                $participant->setParSite(null);
            }
        }

        return $this;
    }
}

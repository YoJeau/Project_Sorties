<?php

namespace App\Entity;

use App\Repository\StateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StateRepository::class)]
class State
{
    const STATE_CREATED = 'En Création';
    const STATE_CANCELLED = 'Annulée';
    const STATE_ARCHIVED = 'Archivée';
    const STATE_OPEN = 'Ouverte';
    const STATE_CLOSED = 'Fermée';
    const STATE_IN_PROGRESS = 'En Cours';
    const STATE_COMPLETED = 'Terminée';
    const STATE_CLOSED_SUBSCRIBE = 'Clôturée';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $staId = null;

    #[ORM\Column(length: 30)]
    private ?string $staLabel = null;

    /**
     * @var Collection<int, Trip>
     */
    #[ORM\OneToMany(targetEntity: Trip::class, mappedBy: 'triState')]
    private Collection $staTrips;

    public function __construct()
    {
        $this->staTrips = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->staId;
    }

    public function getStaLabel(): ?string
    {
        return $this->staLabel;
    }

    public function setStaLabel(string $staLabel): static
    {
        $this->staLabel = $staLabel;

        return $this;
    }

    /**
     * @return Collection<int, Trip>
     */
    public function getStaTrips(): Collection
    {
        return $this->staTrips;
    }

    public function addTrip(Trip $trip): static
    {
        if (!$this->staTrips->contains($trip)) {
            $this->staTrips->add($trip);
            $trip->setTriState($this);
        }

        return $this;
    }

    public function removeTrip(Trip $trip): static
    {
        if ($this->staTrips->removeElement($trip)) {
            // set the owning side to null (unless already changed)
            if ($trip->getTriState() === $this) {
                $trip->setTriState(null);
            }
        }

        return $this;
    }
}

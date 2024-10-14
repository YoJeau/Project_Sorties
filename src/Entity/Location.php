<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $locId = null;

    #[ORM\Column(length: 30)]
    private ?string $locName = null;

    #[ORM\Column(length: 30)]
    private ?string $locStreet = null;

    #[ORM\Column]
    private ?float $locLatitude = null;

    #[ORM\Column]
    private ?float $locLongitude = null;

    /**
     * @var Collection<int, Trip>
     */
    #[ORM\OneToMany(targetEntity: Trip::class, mappedBy: 'triLocation')]
    private Collection $locTrips;

    #[ORM\ManyToOne(inversedBy: 'citLocations')]
    #[ORM\JoinColumn(name: 'loc_city', referencedColumnName: 'cit_id', nullable: false)]
    private ?City $locCity = null;

    public function __construct()
    {
        $this->locTrips = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->locId;
    }

    public function getLocName(): ?string
    {
        return $this->locName;
    }

    public function setLocName(string $locName): static
    {
        $this->locName = $locName;

        return $this;
    }

    public function getLocStreet(): ?string
    {
        return $this->locStreet;
    }

    public function setLocStreet(string $locStreet): static
    {
        $this->locStreet = $locStreet;

        return $this;
    }

    public function getLocLatitude(): ?float
    {
        return $this->locLatitude;
    }

    public function setLocLatitude(float $locLatitude): static
    {
        $this->locLatitude = $locLatitude;

        return $this;
    }

    public function getLocLongitude(): ?float
    {
        return $this->locLongitude;
    }

    public function setLocLongitude(float $locLongitude): static
    {
        $this->locLongitude = $locLongitude;

        return $this;
    }

    /**
     * @return Collection<int, Trip>
     */
    public function getLocTrips(): Collection
    {
        return $this->locTrips;
    }

    public function addTrip(Trip $trip): static
    {
        if (!$this->locTrips->contains($trip)) {
            $this->locTrips->add($trip);
            $trip->setTriLocation($this);
        }

        return $this;
    }

    public function removeTrip(Trip $trip): static
    {
        if ($this->locTrips->removeElement($trip)) {
            // set the owning side to null (unless already changed)
            if ($trip->getTriLocation() === $this) {
                $trip->setTriLocation(null);
            }
        }

        return $this;
    }

    public function getLocCity(): ?City
    {
        return $this->locCity;
    }

    public function setLocCity(?City $locCity): static
    {
        $this->locCity = $locCity;

        return $this;
    }
}

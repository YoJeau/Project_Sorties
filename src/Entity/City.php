<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $citId = null;

    #[Assert\NotBlank(message: "Veuillez renseigner le nom de la ville.")]
    #[Assert\Length(min: 1, max: 30, maxMessage: "Maximum {{ limit }} caractères.")]
    #[ORM\Column(length: 30)]
    private ?string $citName = null;

    #[Assert\NotBlank(message: "Veuillez renseigner le code postal.")]
    #[Assert\Length(min: 1, max: 5, maxMessage: "Maximum {{ limit }} caractères.")]
    #[ORM\Column(length: 5)]
    private ?string $citPostCode = null;

    /**
     * @var Collection<int, Location>
     */
    #[ORM\OneToMany(targetEntity: Location::class, mappedBy: 'locCity')]
    private Collection $citLocations;

    public function __construct()
    {
        $this->citLocations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->citId;
    }

    public function getCitName(): ?string
    {
        return $this->citName;
    }

    public function setCitName(string $citName): static
    {
        $this->citName = $citName;

        return $this;
    }

    public function getCitPostCode(): ?string
    {
        return $this->citPostCode;
    }

    public function setCitPostCode(string $citPostCode): static
    {
        $this->citPostCode = $citPostCode;

        return $this;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getCitLocations(): Collection
    {
        return $this->citLocations;
    }

    public function addLocation(Location $location): static
    {
        if (!$this->citLocations->contains($location)) {
            $this->citLocations->add($location);
            $location->setLocCity($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): static
    {
        if ($this->citLocations->removeElement($location)) {
            // set the owning side to null (unless already changed)
            if ($location->getLocCity() === $this) {
                $location->setLocCity(null);
            }
        }

        return $this;
    }
}

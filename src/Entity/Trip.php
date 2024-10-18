<?php

namespace App\Entity;

use App\Repository\TripRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TripRepository::class)]
class Trip
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $triId = null;
    #[Assert\NotBlank(message: 'Veuillez renseigner le nom de la sortie.')]
    #[Assert\Length(max: 30, maxMessage: "Maximum {{ limit }} caractères.")]
    #[ORM\Column(length: 30)]
    private ?string $triName = null;


    #[Assert\NotBlank(message: 'Veuillez renseigner la date et l\'heure de la sortie.')]
    #[Assert\GreaterThanOrEqual(new \DateTimeImmutable('now'), message: "La date de sortie ne peut pas être inférieur à maintenant.")]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $triStartingDate = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner la durée de la sortie.')]
    #[ORM\Column]
    private ?int $triDuration = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner une date limite d\'inscription.')]
    #[Assert\GreaterThanOrEqual(new \DateTimeImmutable('now'), message: "La date de sortie ne peut pas être inférieur à maintenant.")]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $triClosingDate = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner le nombre de place.')]
    #[ORM\Column]
    private ?int $triMaxInscriptionNumber = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner une description.')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $triDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $triCancellationReason = null;

    /**
     * @var Collection<int, Subscribe>
     */
    #[ORM\OneToMany(targetEntity: Subscribe::class, mappedBy: 'subTripId')]
    private Collection $triSubscribes;

    #[ORM\ManyToOne(inversedBy: 'trips')]
    #[ORM\JoinColumn(name: 'tri_state', referencedColumnName: 'sta_id', nullable: false)]
    private ?State $triState = null;

    #[ORM\ManyToOne(inversedBy: 'trips')]
    #[Assert\NotBlank(message: 'Veuillez renseigner le lieu.')]
    #[ORM\JoinColumn(name: 'tri_location', referencedColumnName: 'loc_id', nullable: false)]
    private ?Location $triLocation = null;

    #[ORM\ManyToOne(inversedBy: 'parCreatedTrips')]
    #[ORM\JoinColumn(name: 'tri_organiser', referencedColumnName: 'par_id', nullable: false)]
    private ?Participant $triOrganiser = null;

    #[ORM\ManyToOne(inversedBy: 'sitTrips')]
    #[Assert\NotBlank(message: 'Veuillez renseigner la ville.')]
    #[ORM\JoinColumn(name: 'tri_site', referencedColumnName: 'sit_id', nullable: false)]
    private ?Site $triSite = null;

    public function __construct()
    {
        $this->triSubscribes = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->triId;
    }

    public function getTriName(): ?string
    {
        return $this->triName;
    }

    public function setTriName(string $triName): static
    {
        $this->triName = $triName;

        return $this;
    }

    public function getTriStartingDate(): ?\DateTimeInterface
    {
        return $this->triStartingDate;
    }

    public function setTriStartingDate(\DateTimeInterface $triStartingDate): static
    {
        $this->triStartingDate = $triStartingDate;

        return $this;
    }

    public function getTriDuration(): ?int
    {
        return $this->triDuration;
    }

    public function setTriDuration(int $triDuration): static
    {
        $this->triDuration = $triDuration;

        return $this;
    }

    public function getTriClosingDate(): ?\DateTimeInterface
    {
        return $this->triClosingDate;
    }

    public function setTriClosingDate(\DateTimeInterface $triClosingDate): static
    {
        $this->triClosingDate = $triClosingDate;

        return $this;
    }

    public function getTriMaxInscriptionNumber(): ?int
    {
        return $this->triMaxInscriptionNumber;
    }

    public function setTriMaxInscriptionNumber(int $triMaxInscriptionNumber): static
    {
        $this->triMaxInscriptionNumber = $triMaxInscriptionNumber;

        return $this;
    }

    public function getTriDescription(): ?string
    {
        return $this->triDescription;
    }

    public function setTriDescription(string $triDescription): static
    {
        $this->triDescription = $triDescription;

        return $this;
    }

    public function getTriCancellationReason(): ?string
    {
        return $this->triCancellationReason;
    }

    public function setTriCancellationReason(?string $triCancellationReason): static
    {
        $this->triCancellationReason = $triCancellationReason;

        return $this;
    }

    /**
     * @return Collection<int, Subscribe>
     */
    public function getTriSubscribes(): Collection
    {
        return $this->triSubscribes;
    }

    public function addSubscribe(Subscribe $subscribe): static
    {
        if (!$this->triSubscribes->contains($subscribe)) {
            $this->triSubscribes->add($subscribe);
            $subscribe->setSubTripId($this);
        }

        return $this;
    }

    public function removeSubscribe(Subscribe $subscribe): static
    {
        if ($this->triSubscribes->removeElement($subscribe)) {
            // set the owning side to null (unless already changed)
            if ($subscribe->getSubTripId() === $this) {
                $subscribe->setSubTripId(null);
            }
        }

        return $this;
    }

    public function getTriState(): ?State
    {
        return $this->triState;
    }

    public function setTriState(?State $triState): static
    {
        $this->triState = $triState;

        return $this;
    }

    public function getTriLocation(): ?Location
    {
        return $this->triLocation;
    }

    public function setTriLocation(?Location $triLocation): static
    {
        $this->triLocation = $triLocation;

        return $this;
    }

    public function getTriOrganiser(): ?Participant
    {
        return $this->triOrganiser;
    }

    public function setTriOrganiser(?Participant $triOrganiser): static
    {
        $this->triOrganiser = $triOrganiser;

        return $this;
    }

    public function getTriSite(): ?Site
    {
        return $this->triSite;
    }

    public function setTriSite(?Site $triSite): static
    {
        $this->triSite = $triSite;

        return $this;
    }
}

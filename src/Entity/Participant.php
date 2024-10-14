<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PAR_USERNAME', fields: ['parUsername'])]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $parId = null;

    #[ORM\Column(length: 180)]
    private ?string $parUsername = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $parPassword = null;

    #[ORM\Column(length: 30)]
    private ?string $parLastName = null;

    #[ORM\Column(length: 30)]
    private ?string $parFirstName = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $parPhone = null;

    #[ORM\Column(length: 50)]
    private ?string $parEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $parPicture = null;

    #[ORM\Column]
    private ?bool $parIsActive;

    /**
     * @var Collection<int, Subscribe>
     */
    #[ORM\OneToMany(targetEntity: Subscribe::class, mappedBy: 'subParticipantId')]
    private Collection $parSubscribes;

    /**
     * @var Collection<int, Trip>
     */
    #[ORM\OneToMany(targetEntity: Trip::class, mappedBy: 'triOrganiser')]
    private Collection $parCreatedTrips;

    #[ORM\ManyToOne(inversedBy: 'sitParticipants')]
    #[ORM\JoinColumn(name: 'par_site', referencedColumnName: 'sit_id', nullable: false)]
    private ?Site $parSite = null;

    public function __construct()
    {
        $this->parIsActive = true;
        $this->parSubscribes = new ArrayCollection();
        $this->parCreatedTrips = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->parId;
    }

    public function getParUsername(): ?string
    {
        return $this->parUsername;
    }

    public function setParUsername(string $parUsername): static
    {
        $this->parUsername = $parUsername;

        return $this;
    }

    public function getParLastName(): ?string
    {
        return $this->parLastName;
    }

    public function setParLastName(?string $parLastName): void
    {
        $this->parLastName = $parLastName;
    }

    public function getParFirstName(): ?string
    {
        return $this->parFirstName;
    }

    public function setParFirstName(?string $parFirstName): void
    {
        $this->parFirstName = $parFirstName;
    }

    public function getParPhone(): ?string
    {
        return $this->parPhone;
    }

    public function setParPhone(?string $parPhone): void
    {
        $this->parPhone = $parPhone;
    }

    public function getParEmail(): ?string
    {
        return $this->parEmail;
    }

    public function setParEmail(?string $parEmail): void
    {
        $this->parEmail = $parEmail;
    }

    public function getParPicture(): ?string
    {
        return $this->parPicture;
    }

    public function setParPicture(?string $parPicture): void
    {
        $this->parPicture = $parPicture;
    }

    public function getParIsActive(): ?bool
    {
        return $this->parIsActive;
    }

    public function setParIsActive(?bool $parIsActive): void
    {
        $this->parIsActive = $parIsActive;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->parUsername;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->parPassword;
    }

    public function setPassword(string $password): static
    {
        $this->parPassword = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Subscribe>
     */
    public function getParSubscribes(): Collection
    {
        return $this->parSubscribes;
    }

    public function addParSubscribe(Subscribe $subscribe): static
    {
        if (!$this->parSubscribes->contains($subscribe)) {
            $this->parSubscribes->add($subscribe);
            $subscribe->setSubParticipantId($this);
        }

        return $this;
    }

    public function removeParSubscribe(Subscribe $subscribe): static
    {
        if ($this->parSubscribes->removeElement($subscribe)) {
            // set the owning side to null (unless already changed)
            if ($subscribe->getSubParticipantId() === $this) {
                $subscribe->setSubParticipantId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Trip>
     */
    public function getParCreatedTrips(): Collection
    {
        return $this->parCreatedTrips;
    }

    public function addCreatedTrip(Trip $trip): static
    {
        if (!$this->parCreatedTrips->contains($trip)) {
            $this->parCreatedTrips->add($trip);
            $trip->setTriOrganiser($this);
        }

        return $this;
    }

    public function removeCreatedTrip(Trip $trip): static
    {
        if ($this->parCreatedTrips->removeElement($trip)) {
            // set the owning side to null (unless already changed)
            if ($trip->getTriOrganiser() === $this) {
                $trip->setTriOrganiser(null);
            }
        }

        return $this;
    }

    public function getParSite(): ?Site
    {
        return $this->parSite;
    }

    public function setParSite(?Site $parSite): static
    {
        $this->parSite = $parSite;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    public const SCOPE_GENERAL = 'general';
    public const SCOPE_SECTOR = 'sector';
    public const SCOPE_CHURCH = 'church';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastModifiedAt = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: true)]  // Changed to allow null for general scope
    private ?Sector $sector = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Attendance::class, orphanRemoval: true)]
    private Collection $attendances;

    #[ORM\ManyToOne]
    private ?User $createdBy = null;

    #[ORM\Column(length: 20)]
    private ?string $scope = 'sector';

    #[ORM\ManyToOne]
    private ?Sector $targetSector = null;

    #[ORM\ManyToOne]
    private ?Church $targetChurch = null;

    /**
     * @var Collection<int, Youth>
     */
    #[Assert\Valid]
    private ?Collection $additionalYouths = null;

    public function __construct()
    {
        $this->attendances = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->additionalYouths = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getLastModifiedAt(): ?\DateTimeInterface
    {
        return $this->lastModifiedAt;
    }

    public function setLastModifiedAt(?\DateTimeInterface $lastModifiedAt): static
    {
        $this->lastModifiedAt = $lastModifiedAt;
        return $this;
    }

    public function getSector(): ?Sector
    {
        return $this->sector;
    }

    public function setSector(?Sector $sector): static
    {
        $this->sector = $sector;
        return $this;
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    public function addAttendance(Attendance $attendance): static
    {
        if (!$this->attendances->contains($attendance)) {
            $this->attendances->add($attendance);
            $attendance->setEvent($this);
        }

        return $this;
    }

    public function removeAttendance(Attendance $attendance): static
    {
        if ($this->attendances->removeElement($attendance)) {
            // set the owning side to null (unless already changed)
            if ($attendance->getEvent() === $this) {
                $attendance->setEvent(null);
            }
        }

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope ?? 'sector';
    }

    public function setScope(?string $scope): static
    {
        $this->scope = $scope ?? 'sector';
        return $this;
    }

    public function getTargetSector(): ?Sector
    {
        return $this->targetSector;
    }

    public function setTargetSector(?Sector $targetSector): static
    {
        $this->targetSector = $targetSector;
        return $this;
    }

    public function getTargetChurch(): ?Church
    {
        return $this->targetChurch;
    }

    public function setTargetChurch(?Church $targetChurch): static
    {
        $this->targetChurch = $targetChurch;
        return $this;
    }

    /**
     * @return Collection<int, Youth>|null
     */
    public function getAdditionalYouths(): ?Collection
    {
        return $this->additionalYouths;
    }

    public function setAdditionalYouths(?Collection $youths): static
    {
        $this->additionalYouths = $youths;
        return $this;
    }

    public function addAdditionalYouth(Youth $youth): static
    {
        if (!$this->additionalYouths->contains($youth)) {
            $this->additionalYouths->add($youth);
        }
        return $this;
    }

    public function removeAdditionalYouth(Youth $youth): static
    {
        $this->additionalYouths->removeElement($youth);
        return $this;
    }

    public function canBeModified(): bool
    {
        $now = new \DateTime();
        $limitDate = $this->createdAt->modify('+4 days');
        return $now <= $limitDate;
    }

    public function isLocked(): bool
    {
        return !$this->canBeModified();
    }
}
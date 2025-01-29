<?php

namespace App\Entity;

use App\Repository\ChurchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChurchRepository::class)]
class Church
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\ManyToOne(inversedBy: 'churches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sector $sector = null;

    #[ORM\OneToMany(mappedBy: 'church', targetEntity: Youth::class)]
    private Collection $youths;

    public function __construct()
    {
        $this->youths = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
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
     * @return Collection<int, Youth>
     */
    public function getYouths(): Collection
    {
        return $this->youths;
    }

    public function addYouth(Youth $youth): static
    {
        if (!$this->youths->contains($youth)) {
            $this->youths->add($youth);
            $youth->setChurch($this);
        }

        return $this;
    }

    public function removeYouth(Youth $youth): static
    {
        if ($this->youths->removeElement($youth)) {
            // set the owning side to null (unless already changed)
            if ($youth->getChurch() === $this) {
                $youth->setChurch(null);
            }
        }

        return $this;
    }
} 
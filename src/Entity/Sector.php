<?php

namespace App\Entity;

use App\Repository\SectorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SectorRepository::class)]
class Sector
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'sector', targetEntity: Church::class, orphanRemoval: true)]
    private Collection $churches;

    #[ORM\OneToMany(mappedBy: 'sector', targetEntity: User::class)]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'sector', targetEntity: Event::class)]
    private Collection $events;

    public function __construct()
    {
        $this->churches = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->events = new ArrayCollection();
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

    /**
     * @return Collection<int, Church>
     */
    public function getChurches(): Collection
    {
        return $this->churches;
    }

    public function addChurch(Church $church): static
    {
        if (!$this->churches->contains($church)) {
            $this->churches->add($church);
            $church->setSector($this);
        }

        return $this;
    }

    public function removeChurch(Church $church): static
    {
        if ($this->churches->removeElement($church)) {
            // set the owning side to null (unless already changed)
            if ($church->getSector() === $this) {
                $church->setSector(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setSector($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getSector() === $this) {
                $user->setSector(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setSector($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getSector() === $this) {
                $event->setSector(null);
            }
        }

        return $this;
    }
} 
<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game implements \ArrayAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Team $teamOne;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Team $teamTwo;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->teamOne = new Team();
        $this->teamTwo = new Team();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeamOne(): Team
    {
        return $this->teamOne;
    }

    public function setTeamOne(Team $teamOne): static
    {
        $this->teamOne = $teamOne;

        return $this;
    }

    public function getTeamTwo(): Team
    {
        return $this->teamTwo;
    }

    public function setTeamTwo(Team $teamTwo): static
    {
        $this->teamTwo = $teamTwo;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[\Override] public function offsetExists(mixed $offset): bool
    {
        return property_exists($this, $offset);
    }

    #[\Override] public function offsetGet(mixed $offset): mixed
    {
        return $this->$offset;
    }

    #[\Override] public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->$offset = $value;
    }

    #[\Override] public function offsetUnset(mixed $offset): void
    {
        unset($this->$offset);
    }
}

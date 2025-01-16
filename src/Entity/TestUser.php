<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TestUserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestUserRepository::class)]
class TestUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?test $test = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $user = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $score = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datetime_start = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datetime_finish = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTest(): ?test
    {
        return $this->test;
    }

    public function setTest(?test $test): static
    {
        $this->test = $test;

        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(?float $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getDatetimeStart(): ?\DateTimeInterface
    {
        return $this->datetime_start;
    }

    public function setDatetimeStart(?\DateTimeInterface $datetime_start): static
    {
        $this->datetime_start = $datetime_start;

        return $this;
    }

    public function getDatetimeFinish(): ?\DateTimeInterface
    {
        return $this->datetime_finish;
    }

    public function setDatetimeFinish(?\DateTimeInterface $datetime_finish): static
    {
        $this->datetime_finish = $datetime_finish;

        return $this;
    }
}

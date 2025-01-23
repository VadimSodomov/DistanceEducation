<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, unique: true)]
    private ?string $code = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'course', cascade: ['persist', 'remove'])]
    #[Orm\OrderBy(["createdAt" => "ASC"])]
    private Collection $lessons;

    #[Ignore]
    #[ORM\OneToMany(targetEntity: CourseUser::class, mappedBy: 'course', cascade: ['persist', 'remove'])]
    private Collection $courseUsers;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
        $this->courseUsers = new ArrayCollection();
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

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

    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function getCourseUsers(): Collection
    {
        return $this->courseUsers;
    }

    public function update(User $author, string $name, ?string $description=null, ?string $code=null): void
    {
        $this->author = $author;
        $this->name = $name;

        if ($description !== null) {
            $this->description = $description;
        }
        if ($code !== null) {
            $this->code = $code;
        }
    }
}

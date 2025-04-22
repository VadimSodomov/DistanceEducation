<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Ignore]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $hwDeadline = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $video = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Ignore]
    #[ORM\OneToMany(targetEntity: LessonUser::class, mappedBy: 'lesson', cascade: ['persist', 'remove'])]
    private Collection $lessonUser;

    /**
     * @var Collection<int, LessonFile>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: LessonFile::class, mappedBy: 'lesson', cascade: ['persist', 'remove'])]
    private Collection $lessonFiles;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));
        $this->lessonUser = new ArrayCollection();
        $this->lessonFiles = new ArrayCollection();
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

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;

        return $this;
    }

    public function getHwDeadline(): ?\DateTimeInterface
    {
        return $this->hwDeadline;
    }

    public function setHwDeadline(?\DateTimeInterface $hwDeadline): static
    {
        $this->hwDeadline = $hwDeadline;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): static
    {
        $this->video = $video;

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

    public function getLessonUser(): Collection
    {
        return $this->lessonUser;
    }
    
    public function update(string $name, Course $course, ?string $description, ?\DateTimeInterface $hwDeadline): void
    {
        $this->name = $name;
        $this->course = $course;
        if ($description !== null) {
            $this->description = $description;
        }
        if ($hwDeadline !== null) {
            $this->hwDeadline = $hwDeadline;
        }
    }

    /**
     * @return Collection<int, LessonFile>
     */
    public function getLessonFiles(): Collection
    {
        return $this->lessonFiles;
    }

    public function addLessonFile(LessonFile $lessonFile): static
    {
        if (!$this->lessonFiles->contains($lessonFile)) {
            $this->lessonFiles->add($lessonFile);
            $lessonFile->setLesson($this);
        }

        return $this;
    }

    public function removeLessonFile(LessonFile $lessonFile): static
    {
        if ($this->lessonFiles->removeElement($lessonFile)) {
            // set the owning side to null (unless already changed)
            if ($lessonFile->getLesson() === $this) {
                $lessonFile->setLesson(null);
            }
        }

        return $this;
    }
}

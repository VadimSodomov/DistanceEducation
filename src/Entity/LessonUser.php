<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LessonUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: LessonUserRepository::class)]
class LessonUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lesson $lesson = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $score = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $uploaded_at = null;

    /**
     * @var Collection<int, LessonUserFile>
     */
    #[ORM\OneToMany(targetEntity: LessonUserFile::class, mappedBy: 'lessonUser', cascade: ['persist', 'remove'])]
    private Collection $lessonUserFiles;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    public function __construct()
    {
        $this->lessonUserFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeInterface
    {
        return $this->uploaded_at;
    }

    public function setUploadedAt(string $uploaded_at = 'now'): static
    {
        $this->uploaded_at = new \DateTime($uploaded_at);
        return $this;
    }

    /**
     * @return Collection<int, LessonUserFile>
     */
    public function getLessonUserFiles(): Collection
    {
        return $this->lessonUserFiles;
    }

    public function addLessonUserFile(LessonUserFile $lessonUserFile): static
    {
        if (!$this->lessonUserFiles->contains($lessonUserFile)) {
            $this->lessonUserFiles->add($lessonUserFile);
            $lessonUserFile->setLessonUser($this);
        }

        return $this;
    }

    public function removeLessonUserFile(LessonUserFile $lessonUserFile): static
    {
        if ($this->lessonUserFiles->removeElement($lessonUserFile)) {
            // set the owning side to null (unless already changed)
            if ($lessonUserFile->getLessonUser() === $this) {
                $lessonUserFile->setLessonUser(null);
            }
        }

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}

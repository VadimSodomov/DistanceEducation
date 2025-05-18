<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LessonFileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: LessonFileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class LessonFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'lessonFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lesson $lesson = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[Ignore]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $nameOnServer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Ignore]
    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;

        return $this;
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

    public function getNameOnServer(): ?string
    {
        return $this->nameOnServer;
    }

    public function setNameOnServer(string $nameOnServer): static
    {
        $this->nameOnServer = $nameOnServer;

        return $this;
    }

    #[ORM\PreRemove]
    public function removeFile(): void
    {
        $dir = dirname(__DIR__, 2) . '/public/uploads/lesson';

        $filePath = $dir . '/' . $this->nameOnServer;

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}

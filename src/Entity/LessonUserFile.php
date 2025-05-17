<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LessonUserFileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: LessonUserFileRepository::class)]
class LessonUserFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[Ignore]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $nameOnServer = null;

    #[ORM\ManyToOne(inversedBy: 'lessonUserFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LessonUser $lessonUser = null;

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

    #[Ignore]
    public function getLessonUser(): ?LessonUser
    {
        return $this->lessonUser;
    }

    public function setLessonUser(?LessonUser $lessonUser): static
    {
        $this->lessonUser = $lessonUser;

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
}

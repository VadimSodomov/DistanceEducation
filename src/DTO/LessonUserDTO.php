<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class LessonUserDTO
{
    #[Assert\Type(type: 'int', message: 'Id должен быть целым числом')]
    public ?int $lessonId;

    #[Assert\NotBlank(message: 'Комментарий обязателен')]
    #[Assert\Type(type: 'string')]
    public ?string $comment;

    public function __construct(
        ?int    $lessonId = null,
        ?string $comment = null,
    )
    {
        $this->lessonId = $lessonId;
        $this->comment = $comment;
    }
}
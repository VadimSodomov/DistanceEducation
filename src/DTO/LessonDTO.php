<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class LessonDTO
{
    #[Assert\NotBlank(message: 'Название урока не указано')]
    #[Assert\Type(type: 'string')]
    public string $name;

    #[Assert\Type(type: 'string')]
    #[Assert\Null]
    public ?string $description;

    #[Assert\NotNull(message: 'ID курса не указан')]
    #[Assert\Type(type: 'int')]
    public int $courseId;

    #[Assert\Regex(
        pattern: '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}$/',
        message: 'Некорректный формат даты. Ожидается формат "дд-мм-гггг чч:мм:сс".'
    )]
    #[Assert\Type(type: 'string')]
    #[Assert\Null]
    public ?string $hwDeadline;

    public function __construct(
        string $name,
        ?string $description = null,
        int $courseId,
        ?string $hwDeadline = null
    ) {
        $this->name = trim($name);
        $this->description = $description !== null ? trim($description) : null;
        $this->courseId = $courseId;
        $this->hwDeadline = $hwDeadline !== null ? trim($hwDeadline) : null;
    }
}
<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class LessonDTO
{
    #[Assert\NotBlank(message: 'Название урока не указано')]
    #[Assert\Type(type: 'string')]
    public string $name;

    #[Assert\Type(type: 'int')]
    public ?int $courseId;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 400, maxMessage: 'Превышено допустимое количество символов (400)')]
    public ?string $description;

    #[Assert\Regex(
        pattern: '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}$/',
        message: 'Некорректный формат даты. Ожидается формат "дд-мм-гггг чч:мм:сс".'
    )]
    #[Assert\Type(type: 'string')]
    public ?string $hwDeadline;

    public function __construct(
        string  $name,
        ?int    $courseId = null,
        ?string $description = null,
        ?string $hwDeadline = null
    )
    {
        $this->name = trim($name);
        $this->courseId = $courseId;
        $this->description = $description !== null ? trim($description) : null;
        $this->hwDeadline = $hwDeadline !== null ? trim($hwDeadline) : null;
    }
}
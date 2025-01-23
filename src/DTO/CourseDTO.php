<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CourseDTO
{
    #[Assert\NotBlank(message: 'Название курса не указано')]
    #[Assert\Type(type: 'string')]
    public string $name;

    #[Assert\Type(type: 'string')]
    public ?string $description;

    public function __construct(string $name, ?string $description = null)
    {
        $this->name = trim($name);

        if ($description !== null) {
            $this->description = trim($description);
        }
    }
}
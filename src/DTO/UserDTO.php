<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UserDTO
{
    #[Assert\NotBlank(message: 'Имя пользователя не указано')]
    #[Assert\Type(type: 'string')]
    public string $name;

    public function __construct(
        string $name,
    )
    {
        $this->name = trim($name);
    }
}
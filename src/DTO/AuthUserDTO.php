<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class AuthUserDTO
{
    #[Assert\NotBlank(message: 'Не указан email')]
    #[Assert\Email(message: 'Неверный формат почты')]
    #[Assert\Type('string')]
    public string $email;

    #[Assert\Type('string')]
    #[Assert\Length(
        min: 5,
        max: 12,
        minMessage: 'Пароль не должен быть меньше 5 символов',
        maxMessage: 'Пароль не должен превышать 12 символов'
    )]
    public string $password;

    public function __construct(
        string $email,
        string $password,
    )
    {
        $this->email = trim($email);
        $this->password = trim($password);
    }
}
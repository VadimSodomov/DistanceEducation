<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class AuthUserDTO
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Type('string')]
    public string $email;

    #[Assert\Type('string')]
    #[Assert\Length(min: 5)]
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
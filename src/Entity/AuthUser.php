<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\RoleEnum;
use App\Repository\AuthUserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AuthUserRepository::class)]
#[ORM\Table(name: 'auth_user')]
#[ORM\UniqueConstraint(name: 'unique_email', columns: ['email'])]
class AuthUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $password = null;

    #[ORM\Column(type: Types::JSON, options: ['default' => '["ROLE_USER"]'])]
    private array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function eraseCredentials(): void
    {
    }

    public function update(
        string                      $email,
        ?string                     $password,
        UserPasswordHasherInterface $passwordHasher
    ): void
    {
        $this->email = $email;
        if (!is_null($password)) {
            $this->password = $passwordHasher->hashPassword($this, $password);
        }
    }

    public function addRole(RoleEnum $role): static
    {
        if (!in_array($role->value, $this->roles)) {
            $this->roles[] = $role->value;
        }

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AuthUser;
use App\Entity\User;
use App\Repository\UserRepository;

trait SecurityTrait
{
    private ?User $user = null;

    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    private function getCurrentUser(): User
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $authUser = $this->getUser();
        if (!$authUser instanceof AuthUser) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->userRepository->findOneBy(['authUser' => $authUser]);
        if ($user === null) {
            throw $this->createNotFoundException();
        }

        $this->user = $user;
        return $user;
    }
}
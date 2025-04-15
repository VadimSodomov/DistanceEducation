<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AuthUserDTO;
use App\DTO\UserDTO;
use App\Entity\AuthUser;
use App\Entity\User;
use App\Enum\RoleEnum;
use App\Repository\AuthUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(
        readonly private AuthUserRepository          $authUserRepository,
        readonly private UserPasswordHasherInterface $passwordHasher,
        readonly private EntityManagerInterface      $entityManager,
    )
    {
    }

    #[Route('/api/login', name: 'api_login_process', methods: ['POST'])]
    public function loginProcess(
        Security                         $security,
        #[MapRequestPayload] AuthUserDTO $authDTO
    ): Response
    {
        return $this->loginHandler($authDTO, RoleEnum::USER->value, $security);
    }

    #[Route('/api/register', name: 'api_register_process', methods: ['POST'])]
    public function registerProcess(
        JWTTokenManagerInterface         $jwtManager,
        #[MapRequestPayload] UserDTO     $userDTO,
        #[MapRequestPayload] AuthUserDTO $authDTO
    ): Response
    {
        $authUser = $this->authUserRepository->findOneByEmail($authDTO->email);

        if ($authUser !== null) {
            return $this->json(['message' => 'Пользователь уже существует'], Response::HTTP_BAD_REQUEST);
        }

        $authUser = new AuthUser();
        $authUser->update($authDTO->email, $authDTO->password, $this->passwordHasher);
        $authUser->addRole(RoleEnum::USER);
        $this->entityManager->persist($authUser);

        $user = new User();
        $user->setName($userDTO->name);
        $user->setAuthUser($authUser);
        $this->entityManager->persist($user);

        $this->entityManager->flush();

        $token = $jwtManager->create($authUser);

        return $this->json(
            [
                'message' => 'Пользователь успешно создан',
                'token' => $token,
            ],
            Response::HTTP_CREATED
        );
    }

    private function loginHandler(
        AuthUserDTO $authDTO,
        string      $role,
        Security    $security
    ): JsonResponse
    {
        $user = $this->authUserRepository->findOneByEmail($authDTO->email);

        if (is_null($user) || !in_array($role, $user->getRoles())) {
            return $this->json(['message' => 'Пользователь не найден'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $authDTO->password)) {
            return $this->json(['message' => 'Неверный пароль'], Response::HTTP_UNAUTHORIZED);
        }

        $security->login($user);
        return $this->json(['data' => 'success'], Response::HTTP_OK);
    }
}

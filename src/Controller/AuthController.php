<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AuthUserDTO;
use App\DTO\UserDTO;
use App\Entity\AuthUser;
use App\Entity\User;
use App\Enum\RoleEnum;
use App\Repository\AuthUserRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    use SecurityTrait;

    public function __construct(
        readonly private AuthUserRepository          $authUserRepository,
        readonly private UserPasswordHasherInterface $passwordHasher,
        readonly private EntityManagerInterface      $entityManager,
        readonly private UserRepository              $userRepository,
    )
    {
    }

    #[Route('/api/login', name: 'api_login_process', methods: ['POST'])]
    public function loginProcess()
    {
    }

    #[Route('/api/register', name: 'api_register_process', methods: ['POST'])]
    public function registerProcess(
        JWTTokenManagerInterface         $jwtManager,
        #[MapRequestPayload] UserDTO     $userDTO,
        #[MapRequestPayload] AuthUserDTO $authDTO
    ): JsonResponse
    {
        $authUser = $this->authUserRepository->findOneByEmail($authDTO->email);

        if ($authUser !== null) {
            return $this->json(['error' => 'Пользователь с этой почтой уже существует'], Response::HTTP_CONFLICT);
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

    #[Route('api/user', name: 'api_user', methods: ['GET'], format: 'json')]
    public function currentUser(): JsonResponse
    {
        return $this->json(['user' => $this->getCurrentUser()]);
    }
}

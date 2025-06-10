<?php

namespace App\Tests\Controller;

use App\Entity\AuthUser;
use App\Entity\User;
use App\Repository\AuthUserRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends WebTestCase
{
    private $client;
    private $authUserRepository;
    private $userRepository;
    private $entityManager;
    private $passwordHasher;
    private $jwtManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->authUserRepository = $this->createMock(AuthUserRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->jwtManager = $this->createMock(JWTTokenManagerInterface::class);

        $container = self::getContainer();
        $container->set(AuthUserRepository::class, $this->authUserRepository);
        $container->set(UserRepository::class, $this->userRepository);
        $container->set(EntityManagerInterface::class, $this->entityManager);
        $container->set(UserPasswordHasherInterface::class, $this->passwordHasher);
        $container->set(JWTTokenManagerInterface::class, $this->jwtManager);
    }

    public function testSuccessfulRegistration(): void
    {
        $this->authUserRepository->method('findOneByEmail')
            ->willReturn(null);

        $this->passwordHasher->method('hashPassword')
            ->willReturn('hashed_password');

        $this->jwtManager->method('create')
            ->willReturn('jwt_token');

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@example.com',
                'password' => 'password123',
                'name' => 'Test User'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Пользователь успешно создан', $responseData['message']);
        $this->assertEquals('jwt_token', $responseData['token']);
    }

    public function testRegistrationWithExistingEmail(): void
    {
        $existingUser = new AuthUser();
        $this->authUserRepository->method('findOneByEmail')
            ->willReturn($existingUser);

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'existing@example.com',
                'password' => 'password123',
                'name' => 'Test User'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Пользователь с этой почтой уже существует', $responseData['error']);
    }

    public function testGetCurrentUser(): void
    {
        $authUser = new AuthUser();
        $authUser->setEmail('test@example.com');

        $user = new User();
        $user->setName('Test User');
        $user->setAuthUser($authUser);

        $this->userRepository->method('findOneBy')
            ->willReturn($user);

        $this->client->loginUser($authUser);

        $this->client->request('GET', '/api/user');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Test User', $responseData['user']['name']);
    }

    public function testGetCurrentUserUnauthorized(): void
    {
        $this->client->request('GET', '/api/user');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $authUser = new AuthUser();
        $authUser->setEmail('test@example.com');
        $authUser->setPassword('hashed_password');

        $this->authUserRepository->method('findOneByEmail')
            ->willReturn($authUser);

        $this->passwordHasher->method('isPasswordValid')
            ->willReturn(false);

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@example.com',
                'password' => 'wrong_password'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
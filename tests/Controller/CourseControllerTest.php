<?php

namespace App\Tests\Controller;

use App\Entity\AuthUser;
use App\Entity\Course;
use App\Entity\CourseUser;
use App\Entity\User;
use App\Repository\AuthUserRepository;
use App\Repository\CourseRepository;
use App\Repository\CourseUserRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CourseControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $authUserRepository;
    private $userRepository;
    private $courseRepository;
    private $courseUserRepository;
    private $testAuthUser;
    private $testUser;
    private $testAuthAuthor;
    private $testAuthor;
    private $testCourse;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->entityManager->beginTransaction();

        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->authUserRepository = $this->client->getContainer()->get(AuthUserRepository::class);
        $this->userRepository = $this->client->getContainer()->get(UserRepository::class);
        $this->courseRepository = $this->client->getContainer()->get(CourseRepository::class);
        $this->courseUserRepository = $this->client->getContainer()->get(CourseUserRepository::class);

        // Создаем тестового AuthUser и связанного User
        $this->testAuthUser = new AuthUser();
        $this->testAuthUser->setEmail('testuser@example.com');
        $this->testAuthUser->setPassword('password');
        $this->entityManager->persist($this->testAuthUser);

        $this->testUser = new User();
        $this->testUser->setAuthUser($this->testAuthUser);
        $this->testUser->setName('Первый');
        $this->entityManager->persist($this->testUser);

        // Создаем тестового автора (AuthUser + User)
        $this->testAuthAuthor = new AuthUser();
        $this->testAuthAuthor->setEmail('testauthor@example.com');
        $this->testAuthAuthor->setPassword('password');
        $this->entityManager->persist($this->testAuthAuthor);

        $this->testAuthor = new User();
        $this->testAuthor->setAuthUser($this->testAuthAuthor);
        $this->testAuthor->setName('Второй');
        $this->entityManager->persist($this->testAuthor);

        // Создаем тестовый курс
        $this->testCourse = new Course();
        $this->testCourse->setName('Test Course');
        $this->testCourse->setDescription('Test Description');
        $this->testCourse->setCode('testcode123');
        $this->testCourse->setAuthor($this->testAuthor);
        $this->entityManager->persist($this->testCourse);

        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }

        $this->entityManager->close();
        $this->entityManager = null;
        parent::tearDown();
    }

    public function testGetAllCourses(): void
    {
        // Подписываем пользователя на курс
        $courseUser = new CourseUser();
        $courseUser->setUser($this->testUser);
        $courseUser->setCourse($this->testCourse);
        $this->entityManager->persist($courseUser);
        $this->entityManager->flush();

        $this->client->loginUser($this->testAuthUser);
        $this->client->request('GET', '/api/course/all');

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('coursesUser', $response['data']);
        $this->assertArrayHasKey('coursesAuthored', $response['data']);
        $this->assertCount(1, $response['data']['coursesUser']);
    }

    public function testGetCourseById(): void
    {
        $this->client->loginUser($this->testAuthUser);
        $this->client->request('GET', '/api/course?id='.$this->testCourse->getId());

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertEquals($this->testCourse->getName(), $response['data']['course']['name']);
        $this->assertFalse($response['data']['isConnected']);
        $this->assertFalse($response['data']['isAuthor']);
    }

    public function testGetCourseByCode(): void
    {
        $this->client->loginUser($this->testAuthUser);
        $this->client->request('GET', '/api/course?code='.$this->testCourse->getCode());

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertEquals($this->testCourse->getName(), $response['data']['course']['name']);
    }

    public function testGetCourseNotFound(): void
    {
        $this->client->loginUser($this->testAuthUser);
        $this->client->request('GET', '/api/course?id=999999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCreateCourse(): void
    {
        $this->client->loginUser($this->testAuthUser);
        $this->client->request('POST', '/api/course/create', [], [], [], json_encode([
            'name' => 'New Course',
            'description' => 'New Description'
        ]));

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('New Course', $response['data']['name']);
        $this->assertNotEmpty($response['data']['code']);

        // Проверяем, что курс действительно создан
        $course = $this->courseRepository->findOneBy(['name' => 'New Course']);
        $this->assertNotNull($course);
        $this->assertEquals($this->testUser->getId(), $course->getAuthor()->getId());
    }

    public function testEditCourse(): void
    {
        $this->client->loginUser($this->testAuthAuthor);
        $this->client->request('POST', '/api/course/edit/'.$this->testCourse->getId(), [], [], [], json_encode([
            'name' => 'Updated Course',
            'description' => 'Updated Description'
        ]));

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('Updated Course', $response['data']['name']);

        // Проверяем обновление в базе
        $updatedCourse = $this->courseRepository->find($this->testCourse->getId());
        $this->assertEquals('Updated Course', $updatedCourse->getName());
    }

    public function testEditCourseForbidden(): void
    {
        $this->client->loginUser($this->testAuthUser); // Не автор курса
        $this->client->request('POST', '/api/course/edit/'.$this->testCourse->getId(), [], [], [], json_encode([
            'name' => 'Updated Course',
            'description' => 'Updated Description'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteCourse(): void
    {
        $this->client->loginUser($this->testAuthAuthor);
        $this->client->request('POST', '/api/course/delete/'.$this->testCourse->getId());

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('Курс успешно удален', $response['message']);

        // Проверяем, что курс удален
        $deletedCourse = $this->courseRepository->find($this->testCourse->getId());
        $this->assertNull($deletedCourse);
    }

    public function testDeleteCourseForbidden(): void
    {
        $this->client->loginUser($this->testAuthUser); // Не автор курса
        $this->client->request('POST', '/api/course/delete/'.$this->testCourse->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testSubscribeToCourse(): void
    {
        $this->client->loginUser($this->testAuthUser);
        $this->client->request('POST', '/api/course/subscribe/'.$this->testCourse->getId());

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('Вы записались на курс!', $response['message']);

        // Проверяем подписку
        $courseUser = $this->courseUserRepository->findOneBy([
            'user' => $this->testUser,
            'course' => $this->testCourse
        ]);
        $this->assertNotNull($courseUser);
    }

    public function testSubscribeToOwnCourse(): void
    {
        $this->client->loginUser($this->testAuthAuthor);
        $this->client->request('POST', '/api/course/subscribe/'.$this->testCourse->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testSubscribeTwice(): void
    {
        // Первая подписка
        $this->client->loginUser($this->testAuthUser);
        $this->client->request('POST', '/api/course/subscribe/'.$this->testCourse->getId());
        $this->assertResponseIsSuccessful();

        // Вторая попытка подписки
        $this->client->request('POST', '/api/course/subscribe/'.$this->testCourse->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testUnsubscribeFromCourse(): void
    {
        // Сначала подписываемся
        $courseUser = new CourseUser();
        $courseUser->setUser($this->testUser);
        $courseUser->setCourse($this->testCourse);
        $this->entityManager->persist($courseUser);
        $this->entityManager->flush();

        $this->client->loginUser($this->testAuthUser);
        $this->client->request('POST', '/api/course/unsubscribe/'.$this->testCourse->getId());

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('Вы отписались от курса', $response['message']);

        // Проверяем отписку
        $courseUser = $this->courseUserRepository->findOneBy([
            'user' => $this->testUser,
            'course' => $this->testCourse
        ]);
        $this->assertNull($courseUser);
    }

    public function testUnsubscribeWhenNotSubscribed(): void
    {
        $this->client->loginUser($this->testAuthUser);
        $this->client->request('POST', '/api/course/unsubscribe/'.$this->testCourse->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }
}
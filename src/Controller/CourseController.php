<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CourseDTO;
use App\Entity\Course;
use App\Entity\User;
use App\Helper\HashHelper;
use App\Repository\CourseRepository;
use App\Repository\CourseUserRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class CourseController extends AbstractController
{
    private User $user;

    public function __construct(
        Security                                $security,
        UserRepository                          $userRepository,
        readonly private EntityManagerInterface $entityManager,
        readonly private CourseRepository       $courseRepository,
        readonly private CourseUserRepository   $courseUserRepository,
    )
    {
        $authUser = $security->getUser();
        $user = $userRepository->findOneBy(['authUser' => $authUser]);

        if ($user === null) {
            throw $this->createAccessDeniedException();
        }

        $this->user = $user;
    }

    #[Route('/', name: 'app_course_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('main_page/index.html.twig');
    }

    #[Route('/course/all', name: 'api_course_all', methods: ['GET'], format: 'json')]
    public function getAll(): JsonResponse
    {
        $coursesUser = $this->courseUserRepository->findBy(['user' => $this->user]);
        $coursesAuthored = $this->courseRepository->findBy(['author' => $this->user]);

        return $this->json(
            [
                'coursesUser' => $coursesUser,
                'coursesAuthored' => $coursesAuthored,
            ],
            context: ['ignored_attributes' => ['lessons']]
        );
    }

    #[Route('/course', name: 'api_course_get_one', methods: ['GET'], format: 'json')]
    public function getOne(
        #[MapQueryParameter] ?int    $id,
        #[MapQueryParameter] ?string $code,
    ): JsonResponse
    {
        if ($id !== null) {
            $course = $this->courseRepository->find($id);
        } elseif ($code !== null) {
            $course = $this->courseRepository->findOneBy(['code' => $code]);
        } else {
            return $this->json(['message' => 'Код или id не указаны', Response::HTTP_BAD_REQUEST]);
        }

        if ($course === null) {
            throw $this->createNotFoundException();
        }

        $courseUser = $this->courseUserRepository->findOneBy([
            'user' => $this->user,
            'course' => $course,
        ]);

        return $this->json(
            [
                'data' => [
                    'course' => $course,
                    'isConnected' => $courseUser !== null,
                ]
            ]
        );
    }

    #[Route('/course/create', name: 'api_course_create', methods: ['POST'], format: 'json')]
    public function create(#[MapRequestPayload] CourseDTO $courseDTO): JsonResponse
    {
        $code = HashHelper::getHash($courseDTO->name);

        $course = new Course();
        $course->update($this->user, $courseDTO->name, $courseDTO->description, $code);

        $this->entityManager->persist($course);
        $this->entityManager->flush();

        return $this->json(['data' => ['name' => $course->getName(), 'code' => $code]]);
    }

    #[Route(
        '/course/edit/{id}',
        name: 'api_course_edit',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]
    public function edit(
        Course $course,
        #[MapRequestPayload] CourseDTO $courseDTO
    ): JsonResponse
    {
        $course->update($this->user, $courseDTO->name, $courseDTO->description);
        $this->entityManager->persist($course);
        $this->entityManager->flush();

        return $this->json(['data' => $course]);
    }

    #[Route(
        '/course/delete/{id}',
        name: 'api_course_delete',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]
    public function delete(Course $course): JsonResponse
    {
        $this->entityManager->remove($course);
        $this->entityManager->flush();

        return $this->json(['message' => 'Курс успешно удален']);
    }
}

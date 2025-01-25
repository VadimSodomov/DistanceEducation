<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\DTO\LessonDTO;
use App\Entity\Lesson;
use App\Entity\LessonUser;
use App\Repository\CourseRepository;
use App\Repository\LessonUserRepository;
use App\Repository\LessonRepository;

class LessonController extends AbstractController
{
    public function __construct(
        readonly private CourseRepository $courseRepository,
        readonly private LessonRepository $lessonRepository,
        readonly private LessonUserRepository $lessonUserRepository,
        readonly private EntityManagerInterface $entityManager,
    ){
        
    }

    #[Route('/api/lesson/create', name: 'api_lesson_create', methods: ['POST'], format: 'json')]
    public function create(#[MapRequestPayload] LessonDTO $lessonDTO): JsonResponse
    {
        $lesson = new Lesson();
        $course = $this->courseRepository->find($lessonDTO->courseId);
        // добавить ошибку курс не найден
        $hwDeadline = $lessonDTO->hwDeadline ? new \DateTimeImmutable($lessonDTO->hwDeadline) : null;
        $lesson->update($lessonDTO->name, $course, $lessonDTO->description, $hwDeadline);

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();
    
        return $this->json(['data' => $lesson]);
    }

    #[Route(
        '/api/lesson/delete/{id}',
        name: 'api_lesson_delete',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]

    public function delete(Lesson $lesson): JsonResponse
    {
        $this->entityManager->remove($lesson);
        $this->entityManager->flush();

        return $this->json(['message' => 'Урок успешно удален']);
    }

    #[Route('/api/lesson/statistic', name: 'api_lesson_get_statistic', methods: ['GET'], format: 'json')]
    public function getOne(
        #[MapQueryParameter] ?int    $id
    ): JsonResponse
    {
        $statistic = new LessonUser();
        $statistic = $this->lessonUserRepository->findBy([
            'lesson' => $id
        ]);
        if ($statistic === null) {
            throw $this->createNotFoundException();
        }

        return $this->json([
                'data' => [
                    'statistic' => $statistic
                ]
            ],
            context: ['ignored_attributes' => ['lesson']]
        );
    }
}

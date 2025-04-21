<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\DTO\LessonDTO;
use App\Entity\Lesson;
use App\Repository\CourseRepository;
use App\Repository\LessonUserRepository;

class LessonController extends AbstractController
{
    use SecurityTrait;

    public function __construct(
        readonly private CourseRepository       $courseRepository,
        readonly private LessonUserRepository   $lessonUserRepository,
        readonly private EntityManagerInterface $entityManager,
        readonly private UserRepository         $userRepository,
    )
    {
    }

    #[Route('/api/lesson/create', name: 'api_lesson_create', methods: ['POST'], format: 'json')]
    public function create(#[MapRequestPayload] LessonDTO $lessonDTO): JsonResponse
    {
        if ($lessonDTO->courseId === null) {
            return $this->json(['error' => 'Id курса не задан'], Response::HTTP_BAD_REQUEST);
        }

        $course = $this->courseRepository->find($lessonDTO->courseId);

        if ($course === null) {
            return $this->json(['error' => 'Курс не найден'], Response::HTTP_NOT_FOUND);
        }

        if ($course->getAuthor() !== $this->getCurrentUser()) {
            throw $this->createAccessDeniedException();
        }

        $lesson = new Lesson();
        $hwDeadline = $lessonDTO->hwDeadline ? new \DateTimeImmutable($lessonDTO->hwDeadline) : null;
        $lesson->update($lessonDTO->name, $course, $lessonDTO->description, $hwDeadline);

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        return $this->json(['id' => $lesson->getId()], Response::HTTP_CREATED);
    }

    #[Route(
        '/api/lesson/edit/{id}',
        name: 'api_lesson_edit',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]
    public function edit(
        Lesson $lesson,
        #[MapRequestPayload] LessonDTO $lessonDTO
    ): JsonResponse
    {
        if ($lesson->getCourse()->getAuthor() !== $this->getCurrentUser()) {
            throw $this->createAccessDeniedException();
        }

        $hwDeadline = $lessonDTO->hwDeadline !== null ? new \DateTimeImmutable($lessonDTO->hwDeadline) : null;

        $lesson->setName($lessonDTO->name);
        $lesson->setHwDeadline($hwDeadline);
        $lesson->setDescription($lessonDTO->description);

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        return $this->json(['message' => 'Урок успешно изменен!'], Response::HTTP_OK);
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
        if ($lesson->getCourse()->getAuthor() !== $this->getCurrentUser()) {
            throw $this->createAccessDeniedException();
        }

        $this->entityManager->remove($lesson);
        $this->entityManager->flush();

        return $this->json(['message' => 'Урок успешно удален'], Response::HTTP_OK);
    }

    #[Route(
        '/api/lesson/{id}/statistic',
        name: 'api_lesson_statistic',
        requirements: ['id' => '\d+'],
        methods: ['GET'],
        format: 'json'
    )]
    public function statisticByLesson(Lesson $lesson): JsonResponse
    {
        $statistic = $this->lessonUserRepository->findBy(
            ['lesson' => $lesson->getId()],
            ['score' => 'DESC']
        );

        return $this->json(['statistic' => $statistic], Response::HTTP_OK);
    }
}

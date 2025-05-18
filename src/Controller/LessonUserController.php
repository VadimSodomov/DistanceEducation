<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\LessonUserDTO;
use App\DTO\ScoreDTO;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\LessonUser;
use App\Repository\CourseUserRepository;
use App\Repository\LessonRepository;
use App\Repository\LessonUserRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class LessonUserController extends AbstractController
{
    use SecurityTrait;

    public function __construct(
        private readonly UserRepository         $userRepository,
        private readonly LessonRepository       $lessonRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CourseUserRepository   $courseUserRepository,
        private readonly LessonUserRepository   $lessonUserRepository,
    )
    {
    }

    #[Route(
        '/api/lesson-user/my-passed/{id}',
        name: 'api_lesson_user_my_passed',
        requirements: ['id' => '\d+'],
        methods: 'GET',
        format: 'json'
    )]
    public function getMyPassed(Course $course): JsonResponse
    {
        $lessonsUser = $this->lessonUserRepository->findByCourseUser($course, $this->getCurrentUser());

        return $this->json($lessonsUser, Response::HTTP_OK);
    }

    #[Route(
        '/api/lesson-user/all-passed/{id}',
        name: 'api_lesson_user_all_passed',
        requirements: ['id' => '\d+'],
        methods: 'GET',
        format: 'json'
    )]
    public function getAllPassed(Lesson $lesson): JsonResponse
    {
        if ($lesson->getCourse()->getAuthor() !== $this->getCurrentUser()) {
            throw $this->createAccessDeniedException();
        }

        $lessonsUserChecked = $this->lessonUserRepository->findByLessonChecked($lesson);
        $lessonsUserNotChecked = $this->lessonUserRepository->findByLessonNotChecked($lesson);

        return $this->json(
            [
                'checked' => $lessonsUserChecked,
                'notChecked' => $lessonsUserNotChecked,
            ],
            Response::HTTP_OK, context: ['ignored_attributes' => ['lesson']]
        );
    }

    #[Route(
        '/api/lesson-user/create',
        name: 'api_lesson_user_create',
        methods: ['POST'],
        format: 'json'
    )]
    public function create(
        #[MapRequestPayload] LessonUserDTO $lessonUserDTO
    ): JsonResponse
    {
        if ($lessonUserDTO->lessonId === null) {
            return $this->json(['error' => 'Id занятия не указан'], Response::HTTP_BAD_REQUEST);
        }

        $lesson = $this->lessonRepository->find($lessonUserDTO->lessonId);

        if ($lesson === null) {
            return $this->json(['error' => 'Занятие не найдено'], Response::HTTP_NOT_FOUND);
        }

        if (
            !$this->courseUserRepository->isParticipant(
                $lesson->getCourse()->getId(),
                $this->getCurrentUser()->getId()
            )
        ) {
            throw $this->createAccessDeniedException();
        }

        if (
            $this->lessonUserRepository->findOneBy(
                ['lesson' => $lesson, 'user' => $this->getCurrentUser()]
            ) !== null) {
            return $this->json(['error' => 'Вы уже прикрепили ответ к этому уроку!'], Response::HTTP_CONFLICT);
        }

        $lessonUser = (new LessonUser())
            ->setLesson($lesson)
            ->setUser($this->getCurrentUser())
            ->setComment($lessonUserDTO->comment)
            ->setUploadedAt();

        $this->entityManager->persist($lessonUser);
        $this->entityManager->flush();

        return $this->json(['message' => 'Ваш ответ прикреплен!'], Response::HTTP_CREATED);
    }

    #[Route(
        '/api/lesson-user/edit/{id}',
        name: 'api_lesson_user_edit',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]
    public function edit(
        LessonUser                         $lessonUser,
        #[MapRequestPayload] LessonUserDTO $lessonUserDTO): JsonResponse
    {
        if ($lessonUser->getUser() !== $this->getCurrentUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($lessonUser->getScore() !== null) {
            return $this->json(
                ['error' => 'Нельзя редактировать ответ после проверки!'],
                Response::HTTP_CONFLICT
            );
        }

        $lessonUser->setComment($lessonUserDTO->comment)
            ->setUploadedAt();

        $this->entityManager->persist($lessonUser);
        $this->entityManager->flush();

        return $this->json(['message' => 'Ваш ответ изменен!'], Response::HTTP_OK);
    }

    #[Route(
        '/api/lesson-user/delete/{id}',
        name: 'api_lesson_user_delete',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]
    public function delete(LessonUser $lessonUser): JsonResponse
    {
        if ($lessonUser->getUser() !== $this->getCurrentUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($lessonUser->getScore() !== null) {
            return $this->json(
                ['error' => 'Нельзя удалить ответ после проверки!'],
                Response::HTTP_CONFLICT
            );
        }

        $this->entityManager->remove($lessonUser);
        $this->entityManager->flush();

        return $this->json(['message' => 'Ваш ответ удален!'], Response::HTTP_OK);
    }

    #[Route(
        '/api/lesson-user/rate/{id}',
        name: 'api_lesson_user_rate',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]
    public function rate(
        LessonUser                    $lessonUser,
        #[MapRequestPayload] ScoreDTO $scoreDTO,
    ): JsonResponse
    {
        if ($lessonUser->getLesson()->getCourse()->getAuthor() !== $this->getCurrentUser()) {
            throw $this->createAccessDeniedException();
        }

        $lessonUser->setScore($scoreDTO->score);

        $this->entityManager->persist($lessonUser);
        $this->entityManager->flush();

        return $this->json(['message' => 'Оценка поставлена!'], Response::HTTP_OK);
    }

    #[Route(
        '/api/lesson-user/unrated/{id}',
        name: 'api_lesson_user_unrated',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]
    public function unrated(
        LessonUser $lessonUser,
    ): JsonResponse
    {
        if ($lessonUser->getLesson()->getCourse()->getAuthor() !== $this->getCurrentUser()) {
            throw $this->createAccessDeniedException();
        }

        $lessonUser->setScore(null);

        $this->entityManager->persist($lessonUser);
        $this->entityManager->flush();

        return $this->json(['message' => 'Оценка отменена!'], Response::HTTP_OK);
    }
}
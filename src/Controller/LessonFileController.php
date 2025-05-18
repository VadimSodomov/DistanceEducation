<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Lesson;
use App\Entity\LessonFile;
use App\Enum\UploadParameterEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class LessonFileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SluggerInterface       $slugger,
    )
    {
    }

    #[Route(
        '/api/upload/lesson/{id}',
        name: 'api_upload_lesson',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]
    public function uploadLesson(Lesson $lesson, Request $request): JsonResponse
    {
        if (!$request->files->count()) {
            return $this->json(
                ['error' => 'Не прикреплено ни одного файла'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $uploadedFiles = $request->files->all();
        $uploadResults = [];
        $hasError = false;

        $uploadDirectory = $this->getParameter(UploadParameterEnum::LESSON->value);

        foreach ($uploadedFiles['files'] as $fieldName => $uploadedFile) {
            try {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename, locale: 'ru');
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                $uploadedFile->move(
                    $uploadDirectory,
                    $newFilename
                );

                $lessonFile = new LessonFile();
                $lessonFile->setLesson($lesson)
                    ->setName($uploadedFile->getClientOriginalName())
                    ->setNameOnServer($newFilename);
                $this->entityManager->persist($lessonFile);
                $this->entityManager->flush();

                $uploadResults[] = [
                    'fieldName' => $fieldName,
                    'originalName' => $uploadedFile->getClientOriginalName(),
                    'status' => 'success'
                ];
            } catch (\Throwable $e) {
                $uploadResults[] = [
                    'fieldName' => $fieldName,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];

                $hasError = true;
            }
        }

        return $this->json([
            'status' => $hasError ? 'error' : 'success',
            'files' => $uploadResults
        ], $hasError ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }

    #[Route(
        '/api/download/lesson-file/{id}',
        name: 'download_lesson_file',
        requirements: ['id' => '\d+'],
        methods: ['GET'],
    )]
    public function downloadFile(LessonFile $lessonFile): BinaryFileResponse
    {
        $uploadDirectory = $this->getParameter(UploadParameterEnum::LESSON->value);

        $filePath = $uploadDirectory . '/' . $lessonFile->getNameOnServer();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Файл не найден');
        }

        $response = new BinaryFileResponse($filePath);

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $lessonFile->getName()
        );

        return $response;
    }

    #[Route(
        '/api/delete/lesson-file/{id}',
        name: 'delete_lesson_file',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]
    public function delete(LessonFile $lessonFile): JsonResponse
    {
        $this->entityManager->remove($lessonFile);
        $this->entityManager->flush();

        return $this->json(['message' => 'Файл успешно удален!'], Response::HTTP_OK);
    }
}
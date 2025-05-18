<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\LessonUser;
use App\Entity\LessonUserFile;
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

class LessonUserFileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SluggerInterface       $slugger,
    )
    {
    }

    #[Route(
        '/api/upload/lesson-user/{id}',
        name: 'api_upload_lesson_user',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]
    public function uploadLessonUser(LessonUser $lessonUser, Request $request): JsonResponse
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

        $uploadDirectory = $this->getParameter(UploadParameterEnum::LESSON_USER->value);

        foreach ($uploadedFiles['files'] as $fieldName => $uploadedFile) {
            try {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename, locale: 'ru');
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                $uploadedFile->move(
                    $uploadDirectory,
                    $newFilename
                );

                $lessonFile = new LessonUserFile();
                $lessonFile->setLessonUser($lessonUser)
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
        '/api/download/lesson-user-file/{id}',
        name: 'download_lesson_user_file',
        requirements: ['id' => '\d+'],
        methods: ['GET'],
    )]
    public function downloadFile(LessonUserFile $lessonUserFile): BinaryFileResponse
    {
        $uploadDirectory = $this->getParameter(UploadParameterEnum::LESSON_USER->value);

        $filePath = $uploadDirectory . '/' . $lessonUserFile->getNameOnServer();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Файл не найден');
        }

        $response = new BinaryFileResponse($filePath);

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $lessonUserFile->getName()
        );

        return $response;
    }

    #[Route(
        '/api/delete/lesson-user-file/{id}',
        name: 'delete_lesson_user_file',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]
    public function delete(LessonUserFile $lessonUserFile): JsonResponse
    {
        $this->entityManager->remove($lessonUserFile);
        $this->entityManager->flush();

        return $this->json(['message' => 'Файл успешно удален!'], Response::HTTP_OK);
    }
}
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\UploadParameterEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class LessonFileController extends AbstractController
{
    #[Route(
        '/api/upload/lesson/{id}',
        name: 'api_upload_lesson',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        format: 'json'
    )]
    public function uploadLesson(Request $request, SluggerInterface $slugger): JsonResponse
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

        foreach ($uploadedFiles as $fieldName => $uploadedFile) {
            try {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                $uploadedFile->move(
                    $uploadDirectory,
                    $newFilename
                );

                $uploadResults[] = [
                    'fieldName' => $fieldName,
                    'originalName' => $uploadedFile->getClientOriginalName(),
                    'fileSize' => $uploadedFile->getSize(),
                    'status' => 'success'
                ];
            } catch (\Throwable $e) {
                $uploadResults[] = [
                    'fieldName' => $fieldName,
                    'originalName' => $uploadedFile->getClientOriginalName(),
                    'fileSize' => $uploadedFile->getSize(),
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];

                $hasError = true;
            }
        }

        return $this->json([
            'status' => $hasError ? 'error' : 'success',
            'files' => $uploadResults
        ]);
    }
}
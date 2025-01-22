<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CourseDTO;
use App\Entity\Course;
use App\Entity\User;
use App\Helper\HashHelper;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CourseController extends AbstractController
{
    private User $user;

    public function __construct(
        Security                                $security,
        UserRepository                          $userRepository,
        readonly private EntityManagerInterface $entityManager,
    )
    {
        $email = $security->getUser()->getUserIdentifier();
        $user = $userRepository->findOneByEmail($email);

        if ($user === null) {
            throw $this->createAccessDeniedException();
        }

        $this->user = $user;
    }

    #[Route('/course', name: 'app_course', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('course/index.html.twig');
    }

    #[Route('/course/create', name: 'api_course_create', methods: ['POST'], format: 'json')]
    public function create(#[MapRequestPayload] CourseDTO $courseDTO): JsonResponse
    {
        $code = HashHelper::getHash($courseDTO->name);

        $course = new Course();
        $course->update($this->user, $courseDTO->name, $courseDTO->description, $code);

        $this->entityManager->persist($course);
        $this->entityManager->flush();

        return $this->json(['data' => [
            'name' => $course->getName(),
            'link' => $this->generateUrl(
                'app_course', ['code' => $code],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestConroller extends AbstractController
{
    #[Route('/test', name: 'test', methods: ['GET'])]
    public function testAction(): Response
    {
        return $this->render('base.html.twig');
    }
}
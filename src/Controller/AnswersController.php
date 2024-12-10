<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnswersController extends AbstractController
{
    #[Route('/answers', name: 'app_answers')]
    public function index(): Response
    {
        return $this->render('answers/index.html.twig', [
            'controller_name' => 'AnswersController',
        ]);
    }
}

<?php

namespace App\Controller;

use App\Repository\QuizzRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{
    public function __construct(
        private QuizzRepository $quizzRepo,
    ) {    
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $quizzes1 = $this->quizzRepo->findAll();
        $quizzes2 = $this->quizzRepo->findBy([],orderBy: ["title" => "DESC"]);
        $quizzes3 = $this->quizzRepo->findBy([],orderBy: ["title" => "ASC"]);

        return $this->render('/index.html.twig', [
            'quizzes1' => $quizzes1,
            'quizzes2' => $quizzes2,
            'quizzes3' => $quizzes3
        ]);
    }
}

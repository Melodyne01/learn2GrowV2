<?php

namespace App\Controller;

use App\Entity\Quizz;
use App\Repository\QuizzRepository;
use App\Repository\CertificationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LeaderboardsController extends AbstractController
{
    public function __construct(
        private QuizzRepository $quizzRepo,
        private CertificationRepository $certificationRepo,
    ) {    
    }
    #[Route('/leaderboards', name: 'app_leaderboards')]
    public function index(): Response
    {
        $leaderboard = $this->certificationRepo->getLeaderboard();

        return $this->render('leaderboards/index.html.twig', [
            'leaderboard' => $leaderboard,
        ]);
    }

    #[Route('/leaderboard/{id}', name: 'app_quizz_leaderboard')]
    public function quizzLeaderboard(Quizz $quizz): Response
    {
        $certifications = $this->certificationRepo->findBy([
            "quizz" => $quizz
        ],
        ["result" => "DESC"]);
        return $this->render('leaderboards/leaderboard.html.twig', [
            'certifications' => $certifications,
            'quizz' => $quizz
        ]);
    }
}

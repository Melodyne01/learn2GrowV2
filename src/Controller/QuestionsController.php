<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\CreateQuestionType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QuestionsController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $manager,
    ) {    
    }

    #[Route('/questions', name: 'app_questions')]
    public function index(): Response
    {
        return $this->render('questions/index.html.twig', [
            'controller_name' => 'QuestionsController',
        ]);
    }

    #[Route('/questions/create', name: 'app_questions_create')]
    public function create(Request $request): Response
    {
        $question = new Question();
        
        $form = $this->createForm(CreateQuestionType::class, $question);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->getManager()->persist($question);
            $this->manager->getManager()->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('questions/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\User;
use App\Entity\Quizz;
use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\UserAnswer;
use App\Entity\Certification;
use App\Form\CreateQuizzType;
use App\Repository\QuizzRepository;
use App\Repository\QuestionRepository;
use App\Repository\UserAnswerRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\CertificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QuizzesController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $manager,
        private QuizzRepository $quizzRepo,
        private QuestionRepository $questionRepo,
        private UserAnswerRepository $userAnswerRepo,
        private CertificationRepository $certificationRepo,
        private int $currentQuestionIndex = 1
    ) {    
    }
    
    #[Route('/quizz/{id}', name: 'app_quizz')]
    public function quizz(Quizz $quizz): Response
    {
        return $this->render('quizzes/quizz.html.twig', [
            'quizz' => $quizz,
        ]);
    }

    #[Route('/quizzes', name: 'app_quizzes')]
    public function index(): Response
    {

        $quizzes = $this->quizzRepo->findAll();

        $quizzesWithCertificationStatus = [];
        foreach ($quizzes as $quiz) {
            $isCertified = $this->certificationRepo->findOneBy([
                'quizz' => $quiz,
                'user' => $this->getUser(),
            ]);

            $quizzesWithCertificationStatus[] = [
                'quizz' => $quiz,
                'isCertified' => (bool)$isCertified,
            ];
        }

        $quizzes = $this->quizzRepo->findAll();
        return $this->render('quizzes/index.html.twig', [
            'quizzes' => $quizzesWithCertificationStatus,
        ]);
    }

    #[Route('/myQuizzes', name: 'app_my_quizzes')]
    public function quizzes(): Response
    {

        $quizzes = $this->quizzRepo->findBy([
            "createdBy" => $this->getUser()
        ]);
        return $this->render('quizzes/myquizzes.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }


    #[Route('/quizzes/create', name: 'app_quizzes_create')]
    public function create(Request $request): Response
    {
        $question = new Question();

        $quizz = new Quizz();
        $quizz->getQuestions()->add($question);

        $form = $this->createForm(CreateQuizzType::class, $quizz);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $quizz->setCreatedBy($this->getUser());
            $question->setQuizz($quizz);
            $quizz->setCreatedAt(new DateTime('now', new DateTimeZone('Europe/Paris')));
            $this->manager->getManager()->persist($quizz);
            $this->manager->getManager()->flush();

            return $this->redirectToRoute('app_quizzes_details', [
                "id" => $quizz->getId()
            ]);
        }
        
        return $this->render('quizzes/addQuizz.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/quizz/{id}/details', name: 'app_quizzes_details')]
    public function details(Quizz $quizz, Request $request): Response
    {
        $form = $this->createForm(CreateQuizzType::class, data: $quizz);

        $form->handleRequest($request);

        

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $quizz->setCreatedAt(new DateTime('now', new DateTimeZone('Europe/Paris')));
            $this->manager->getManager()->persist($quizz);
            $this->manager->getManager()->flush();

            return $this->redirectToRoute('app_profile');
        }
        
        return $this->render('quizzes/detailsQuizz.html.twig', [
            'form' => $form->createView(),
            'quizz' => $quizz,
        ]);
    }


    #[Route('/quizz/{id}/infos', name: 'app_quizzes_infos')]
    public function infos(Quizz $quizz, Request $request): Response
    {
        $firstQuestion = $this->questionRepo->findOneBy([
            "quizz" => $quizz->getId()
        ]);
        return $this->render('quizzes/infosQuizz.html.twig', [
            'quizz' => $quizz,
            'firstQuestion' => $firstQuestion

        ]);
    }
    #[Route('/quiz/{id}/{questionNumber}', name: 'quiz_question', requirements: ['questionNumber' => '\d+'])]
    public function question(
        Quizz $quizz,
        int $questionNumber,
        Request $request,
    ): Response {
        $questions = $quizz->getQuestions()->toArray();

        // $alredyRespondAnswer = $this->userAnswerRepo->findBy([
        //     "quizz" => $quizz,
        //     "user" => $this->getUser()
        // ]);

        // if ($alredyRespondAnswer != null ){
        //     if (!isset($question)) {
        //         return $this->redirectToRoute('quiz_finish', ['id' => $quizz->getId()]);
        //     }
        // }
        $question = $this->questionRepo->findOneBy([
            "id" => $questionNumber
        ]);
        
        $currentIndex = array_search($question, $questions, true);
        
        $isAlreadyCertificate = $this->certificationRepo->findBy([
            'quizz' => $quizz,
            'user' => $this->getUser()
        ]);

        // Vérifier si la question demandée existe
        if (!isset($question)|| $isAlreadyCertificate != null) {
            return $this->redirectToRoute('quiz_finish', ['id' => $quizz->getId()]);
        }

        $answers = $question->getAnswers();

        if ($request->isMethod('POST')) {
            $selectedAnswerId = $request->request->get('answer');
            $selectedAnswer = $this->manager->getRepository(Answer::class)->find($selectedAnswerId);

            // Créer une réponse utilisateur
            if ($selectedAnswer && $selectedAnswer->getQuestion() === $question) {
                $userAnswer = new UserAnswer();
                $userAnswer->setUser($this->getUser()); // Si l'utilisateur est connecté
                $userAnswer->setQuestion($question);
                $userAnswer->setQuizz($quizz);
                $userAnswer->setAnswer($selectedAnswer);
                $userAnswer->setSubmittedAt(new DateTime('now', new DateTimeZone('Europe/Paris')));

                $this->manager->getManager()->persist($userAnswer);
                $this->manager->getManager()->flush();
            }

            // Passer à la question suivante
            return $this->redirectToRoute('quiz_question', [
                'id' => $quizz->getId(),
                'questionNumber' => $questionNumber + 1,
            ]);
        }
    
        return $this->render('quizzes/question.html.twig', [
            'quizz' => $quizz,
            'question' => $question,
            'answers' => $answers,
            'currentQuestionNumber' => $questionNumber,
            'currentIndex' => $currentIndex + 1,
            'totalQuestions' => count($questions),
        ]);
    }

    #[Route('/quiz/{id}/finish', name: 'quiz_finish')]
    public function finish(Quizz $quizz): Response
    {
        $userAnswers = $this->userAnswerRepo->findBy([
            "user" => $this->getUser(),
            "quizz" => $quizz
        ]);
        $totalQuestions = 0;
        $goodAnswers = 0;
        foreach($userAnswers as $answer){
            if($answer->getAnswer()->isGoodAnswer()){
                $goodAnswers++;
            }
            $totalQuestions++;
        }
        $isAlreadyCertificate = $this->certificationRepo->findBy([
            'quizz' => $quizz,
            'user' => $this->getUser()
        ]);
        $result = (int)(($goodAnswers/$totalQuestions)*100);

        if($isAlreadyCertificate == null){
            if($result>=50){
                $certification = new Certification();
                $certification->setQuizz($quizz);
                $certification->setUser($this->getUser());
                $certification->setResult($result);
                $certification->setCreatedAt(new DateTime('now', new DateTimeZone('Europe/Paris')));
                $this->manager->getManager()->persist($certification);
                $this->manager->getManager()->flush();
            }
        }

        return $this->render('quizzes/finish.html.twig', [
            'quizz' => $quizz,
            'result' => $result,
            'showModal' => true,
        ]);
    }
}

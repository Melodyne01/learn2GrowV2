<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Repository\QuizzRepository;
use App\Repository\CategoryRepository;
use App\Repository\CertificationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $manager,
        private UserRepository $userRepo,
        private UserPasswordHasherInterface $passwordHasher,
        private CategoryRepository $categoryRepo,
        private CertificationRepository $certificationRepos,
        private QuizzRepository $quizzRepo
    ) {    
    }
    
    
    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        $myCertificates = $this->certificationRepos->findBy(['user' => $this->getUser()]);
        $myCategories = $this->categoryRepo->findBy(['createdBy' => $this->getUser()]);
        
        return $this->render('users/profile.html.twig', [
            'user' => $this->getUser(),
            'certifications' => $myCertificates,
            'categories' => $myCategories,
        ]);
    }

    #[Route('/myCertificates', name: 'app_my_certificates')]
    public function myCertificates(): Response
    {
        $myCertificates = $this->certificationRepos->findBy(['user' => $this->getUser()]);
        
        return $this->render('users/myCertificates.html.twig', [
            'certifications' => $myCertificates,
        ]);
    }
    
    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        if ($this->getUser()){
            return $this->redirectToRoute('app_profile');
        }
        
        $this->CheckUserInDatabase();
        //Creation d'un nouvel objet User
        $user = new User();
        //Creation du formulaire sur base d'un formulaire crée au préalable 
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        //Vérification de la conformité des données entrées par l'utilisateur
        if ($form->isSubmitted() && $form->isValid()) {
            //Chiffrement du mot de passe selon l'algorytme Bcrypt
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $user->setAccountType("ROLE_ADMIN");
            $user->setCreatedAt(new DateTime('Europe/Paris'));
            $this->manager->getManager()->persist($user);
            //Envoie des données vers la base de données
            $this->manager->getManager()->flush();
            
            $this->addFlash("success", "Le compte à bien été créé");            
        }
        return $this->render('users/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/login', name: 'login')]
    public function index(): Response
    {
        $this->CheckUserInDatabase();
        $currentUser = $this->getUser();
        // Si l'user est déjà connecté
        if (!empty($currentUser)){
            return $this->redirectToRoute('app_home');
        }
        $this->addFlash("danger", "Les informations de connexion ne sont pas valides");
        return $this->redirectToRoute('app_register');
    }

    #[Route('/logout', name: 'logout')]
    public function logout()
    {
        
    }

    public function CheckUserInDatabase()
    {
        $userList = $this->userRepo->findAll();
        //S'il n'y a pas d'users
        if($userList == null){
            $user = new User();
            $user->setFirstname("Tanguy");
            $user->setLastname("Baldewyns");  
            $user->setEmail("tanguy.baldewyns@gmail.com");
            $hashedPassword = $this->passwordHasher->hashPassword($user,"aaaaaa");
            $user->setPassword($hashedPassword);
            $user->setCreatedAt(new DateTime('Europe/Paris'));
            $user->setAccountType("ROLE_ADMIN");
            $this->manager->getManager()->persist($user);
            //Envoie des données vers la base de données
            $this->manager->getManager()->flush(); 
        }
    }
}

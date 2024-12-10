<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Category;
use App\Form\CreateCategoryType;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoriesController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $manager,
        private CategoryRepository $categoryRepo
    ) {    
    }

    #[Route('/categories', name: 'app_categories')]
    public function index(Request $request): Response
    {
        $category = new Category();
        
        $form = $this->createForm(CreateCategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setCreatedAt(new DateTime('now', new DateTimeZone('Europe/Paris')));
            $category->setCreatedBy($this->getUser());
            $this->manager->getManager()->persist($category);
            $this->manager->getManager()->flush();

            return $this->redirectToRoute('app_profile');
        }

        $categories = $this->categoryRepo->findAll();
        return $this->render('categories/index.html.twig', [
            'categories' => $categories,
            'form' => $form
        ]);
    }
}

<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\SubCategory;
use App\Form\CreateSubCategoryType;
use App\Repository\SubCategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubCategoriesController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $manager,
        private SubCategoryRepository $subCategoryRepo
    ) {    
    }
    #[Route('/sub/categories', name: 'app_sub_categories')]
    public function index(Request $request): Response
    {
        $subCategories = $this->subCategoryRepo->findAll();
        $subCategory = new SubCategory();

        $form = $this->createForm(CreateSubCategoryType::class, $subCategory);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if($image){
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );

            $subCategory->setImage($fichier);
            }
            $subCategory->setCreatedAt(new DateTime('now', new DateTimeZone('Europe/Paris')));
            $subCategory->setCreatedBy($this->getUser());
            $this->manager->getManager()->persist($subCategory);
            $this->manager->getManager()->flush();

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('sub_categories/index.html.twig', [
            'form' => $form->createView(),
            'subCategories' => $subCategories
        ]);
    }
}

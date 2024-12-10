<?php

namespace App\Form;

use App\Entity\Quizz;
use App\Entity\Category;
use App\Entity\SubCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CreateQuizzType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subCategory', EntityType::class, [
                'attr' => ['class' => 'uk-select', 'label' => false],
                'class' => SubCategory::class,
                'choice_label' => 'name',
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'uk-textarea uk-resize-vertical', 'label' => false],
            ])
            ->add('title', TextType::class, [
                'attr' => ['class' => 'uk-input', 'label' => false],
            ])
            ->add('dificulty', ChoiceType::class, [
                'choices'  => [
                    'Beginner' => 'Beginner',
                    'Intermediate' => 'Intermediate',
                    'Expert' => 'Expert',
                ],
                'attr' => ['class' => 'uk-input', 'label' => false]
            ])
            ->add('questions', CollectionType::class,[
                'entry_type' => CreateQuestionType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true, // Nécessaire pour JavaScript
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quizz::class,
        ]);
    }
}

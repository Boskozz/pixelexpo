<?php

namespace App\Form;

use App\Entity\Projet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->add('icon', ChoiceType::class, [
                'label' => 'Icône',
                'choices' => [
                    'Soleil' => 'fas fa-sun',
                    'Flocon de glace' => 'fas fa-snowflake',
                    'Monument' => 'fas fa-archway',
                    'Mer' => 'fas fa-anchor',
                    'Android' => 'fab fa-android',
                    'Aroba' => 'fas fa-at'
                ]
            ])
            ->add('color', ChoiceType::class, [
                'choices' => [
                    'Rouge' => 'red',
                    'Bleu' => 'blue',
                    'Vert' => 'green',
                    'Orange' => 'orange',
                    'Violet' => 'violet',
                    'Jaune' => 'yellow',
                    'Gris' => 'grey',
                    'Noir' => 'black',
                ]
            ])
            ->add('imageFile', FileType::class, [
                'required' => false
            ])
            ->add('prive', ChoiceType::class, [
                'label' => 'Accès',
                'choices' => [
                    'Privé' => true,
                    'Public' => false
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
            'translation_domain' => 'forms',
        ]);
    }
}

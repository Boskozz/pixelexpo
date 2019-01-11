<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rating', IntegerType::class, [
                'attr' => [
                    'placeholder' => 'Veuillez indiquer votre note de 0 à 5',
                    'min' => 0,
                    'max' => 5,
                    'step' => 1
                ]
            ])
            ->add('content', TextType::class, [
                'attr' => [
                    'placeholder' => 'Faites un commentaire'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'translation_domain' => 'forms'
        ]);
    }
}

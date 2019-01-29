<?php

namespace App\Form;

use App\Entity\Etiquette;
use App\Entity\Picture;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => "Veuillez indiquez un titre précis"
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => "Vous pouvez décrire précisément votre photo ou ne rien mettre du tout"
                ]
            ])
            ->add('private', ChoiceType::class, [
                'label' => 'Accès',
                'choices' => [
                    'Privé' => true,
                    'Public' => false
                ]
            ])
            ->add('etiquettes', EntityType::class, [
                'class' => Etiquette::class,
                'choice_label' => 'name',
                'multiple' => true,
                'attr' => [
                    'placeholder' => "Choisissez au moins un tag, une étiquette"
                ]
            ])
            ->add('libre', ChoiceType::class, [
                'label' => 'Libre de droit (Permet le téléchargment de l\'image en pleine résolution)',
                'choices' => [
                    'Oui' => true,
                    'Non' => false
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Picture::class,
            'translation_domain' => 'forms'
        ]);
    }
}

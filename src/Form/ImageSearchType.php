<?php

namespace App\Form;

use App\Entity\Etiquette;
use App\Entity\ImageSearch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orientation', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'placeholder' => 'Orientation',
                'choices' => [
                    'Paysage' => 'paysage',
                    'Portrait' => 'portrait'
                ]
            ])
            ->add('etiquettes', EntityType::class, [
                'required' => false,
                'label' => false,
                'class' => Etiquette::class,
                'choice_label' => 'name',
                'multiple' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ImageSearch::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}

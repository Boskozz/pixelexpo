<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, $this->getConfig(null,"Choice an unique login"))
            ->add('firstName', TextType::class, $this->getConfig(null,"Type your first name", ['required' => false]))
            ->add('lastName', TextType::class, $this->getConfig(null,"Type your last name", ['required' => false]))
            ->add('email', EmailType::class, $this->getConfig(null,"Type your email", ['required' => false]))
            ->add('description',TextareaType::class, $this->getConfig(null,"Describe your self", ['required' => false]))
            ->add('password', PasswordType::class, $this->getConfig(null,"Choice a good password"))
            ->add('passwordConfirm', PasswordType::class, $this->getConfig(null,"Verify password", ['required' => true]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms',
            'validation_groups' => 'registration'
        ]);
    }
}

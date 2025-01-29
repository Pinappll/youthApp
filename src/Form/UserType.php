<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Sector;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => $options['is_new'] ?? false,
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Dirigeant' => 'ROLE_DIRIGEANT',
                    'Enseignant' => 'ROLE_ENSEIGNANT'
                ],
                'multiple' => true,
                'expanded' => true,
                'attr' => ['class' => 'mt-1 block w-full']
            ])
            ->add('sector', EntityType::class, [
                'class' => Sector::class,
                'choice_label' => 'name',
                'label' => 'Secteur',
                'attr' => ['class' => 'form-select rounded-md shadow-sm mt-1 block w-full']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_new' => false
        ]);
    }
}

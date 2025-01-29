<?php

namespace App\Form;

use App\Entity\Youth;
use App\Entity\Church;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YouthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('church', EntityType::class, [
                'class' => Church::class,
                'choice_label' => 'name',
                'label' => 'Église',
                'attr' => ['class' => 'form-select rounded-md shadow-sm mt-1 block w-full']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Youth::class,
        ]);
    }
}

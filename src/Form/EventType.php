<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Sector;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;

class EventType extends AbstractType
{
    public function __construct(
        private Security $security
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date et heure',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
        ;

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('sector', EntityType::class, [
                'class' => Sector::class,
                'choice_label' => 'name',
                'label' => 'Secteur',
                'attr' => ['class' => 'form-select rounded-md shadow-sm mt-1 block w-full']
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}

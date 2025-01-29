<?php

namespace App\Form;

use App\Entity\Attendance;
use App\Entity\Event;
use App\Entity\User;
use App\Entity\Youth;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttendanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isPresent', CheckboxType::class, [
                'label' => 'Présent',
                'required' => false,
                'attr' => ['class' => 'h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded']
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => [
                    'class' => 'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md',
                    'rows' => 2
                ]
            ])
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('lastModifiedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'id',
            ])
            ->add('youth', EntityType::class, [
                'class' => Youth::class,
                'choice_label' => 'id',
            ])
            ->add('createdBy', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Attendance::class,
        ]);
    }
}

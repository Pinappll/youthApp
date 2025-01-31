<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Sector;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\Church;
use App\Entity\Youth;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;

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
            ->add('scope', ChoiceType::class, [
                'label' => 'Portée',
                'choices' => [
                    'Général (tous les jeunes)' => Event::SCOPE_GENERAL,
                    'Par secteur' => Event::SCOPE_SECTOR,
                    'Par église' => Event::SCOPE_CHURCH
                ],
                'data' => Event::SCOPE_GENERAL,
                'attr' => [
                    'class' => 'form-select rounded-md shadow-sm mt-1 block w-full'
                ]
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date et heure',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ]);

        // Add dynamic target selector based on scope
        $formModifier = function (FormInterface $form, ?string $scope = null, ?Event $event = null) {
            if ($scope === Event::SCOPE_SECTOR) {
                $form->add('targetSector', EntityType::class, [
                    'class' => Sector::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Sélectionnez un secteur',
                    'label' => 'Secteur cible',
                    'required' => true,
                    'attr' => ['class' => 'form-select rounded-md shadow-sm mt-1 block w-full']
                ]);
            } elseif ($scope === Event::SCOPE_CHURCH) {
                $form->add('targetChurch', EntityType::class, [
                    'class' => Church::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Sélectionnez une église',
                    'label' => 'Église cible',
                    'required' => true,
                    'group_by' => function($church) {
                        return $church->getSector()->getName();
                    },
                    'attr' => ['class' => 'form-select rounded-md shadow-sm mt-1 block w-full']
                ]);
            }
        };

        // Add form event listeners
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $formModifier($event->getForm(), $data?->getScope(), $data);
        });

        $builder->get('scope')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $scope = $event->getForm()->getData();
            $formModifier($event->getForm()->getParent(), $scope);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}

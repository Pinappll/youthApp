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
                'choices' => [
                    'Secteur' => 'sector',
                    'Église' => 'church',
                    'Général' => 'general',
                ],
                'required' => true,
                'placeholder' => false,
                'data' => $builder->getData()->getScope() ?? 'sector',
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date et heure',
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
                'required' => true,
                'attr' => ['class' => 'form-input rounded-md shadow-sm mt-1 block w-full']
            ]);

        // Add form event listeners for dynamic fields
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            
            if ($data && $data->getScope()) {
                $this->addScopeSpecificFields($form, $data->getScope());
            }
        });

        $builder->get('scope')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm()->getParent();
            $scope = $event->getForm()->getData();
            
            if ($form) {
                $this->addScopeSpecificFields($form, $scope);
            }
        });
    }

    private function addScopeSpecificFields(FormInterface $form, ?string $scope): void
    {
        if ($scope === 'sector') {
            $form->add('targetSector', EntityType::class, [
                'class' => Sector::class,
                'choice_label' => 'name',
                'label' => 'Secteur cible',
                'required' => true,
                'attr' => ['class' => 'form-select rounded-md shadow-sm mt-1 block w-full']
            ]);
        } elseif ($scope === 'church') {
            $form->add('targetChurch', EntityType::class, [
                'class' => Church::class,
                'choice_label' => 'name',
                'label' => 'Église cible',
                'required' => true,
                'group_by' => function($church) {
                    return $church->getSector()->getName();
                },
                'attr' => ['class' => 'form-select rounded-md shadow-sm mt-1 block w-full']
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'event_form'
        ]);
    }
}

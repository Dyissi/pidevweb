<?php

namespace App\Form;

use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; 



class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('locationName')
            ->add('locationAddress')
            ->add('locationCity')
            ->add('locationCapacity')
            ->add('locationType', ChoiceType::class, [
                'choices' => [
                    'Outdoor' => 'Outdoor',
                    'Indoor' => 'Indoor',
                ],
                'expanded' => true,  // Changed from false to true
                'multiple' => false,
                'label' => 'Location Type',
                'attr' => [
                    'class' => 'btn-group',
                    'data-toggle' => 'buttons'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}

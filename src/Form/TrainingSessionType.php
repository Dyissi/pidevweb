<?php

namespace App\Form;

use App\Entity\TrainingSession;
use App\Entity\Team;
use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TrainingSessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sessionFocus', ChoiceType::class, [
                'choices' => [
                    'Agility' => 'Agility',
                    'Strength' => 'Strength',
                    'Dribbling' => 'Dribbling',
                    'Endurance' => 'Endurance',
                    'Sprint' => 'Sprint',
                    'Speed' => 'Speed',
                ],
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('sessionStartTime', TimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime',
                'html5' => false,
                'invalid_message' => 'Please enter a valid time (HH:MM)',
                'attr' => ['placeholder' => 'HH:MM'],
            ])
            ->add('sessionDuration', ChoiceType::class, [
                'choices' => [
                    '45 mins' => 45,
                    '60 mins' => 60,
                    '90 mins' => 90,
                    '120 mins' => 120,
                ],
                'expanded' => true,
            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'locationName',
                'placeholder' => 'Select a location',
                'required' => true,
            ])
            ->add('sessionNotes')
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'teamName',
                'placeholder' => 'Select a team',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TrainingSession::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teamName', null, [
                'label' => 'Team Name',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Team name cannot be blank.']),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9\s]+$/',
                        'message' => 'Team name can only contain letters, numbers, and spaces.',
                    ]),
                ],
                'attr' => ['placeholder' => 'Enter team name'],
            ])
            ->add('teamTypeOfSport', ChoiceType::class, [
                'label' => 'Sport',
                'choices' => [
                    'Basketball' => 'Basketball',
                    'Football' => 'Football',
                    'Volleyball' => 'Volleyball',
                    'Handball' => 'Handball',
                ],
                'placeholder' => 'Select a sport',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please select a sport.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
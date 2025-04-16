<?php

namespace App\Form;

use App\Entity\Tournament;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;

class TournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Tournament Name
            ->add('tournamentName', null, [
                'label' => 'Tournament Name',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Tournament name cannot be blank.']),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9\s]+$/',
                        'message' => 'Tournament name can only contain letters, numbers, and spaces.',
                    ]),
                ],
                'attr' => ['placeholder' => 'Enter tournament name'],
            ])

            // Tournament Location
            ->add('tournamentLocation', null, [
                'label' => 'Location',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Location cannot be blank.']),
                ],
                'attr' => ['placeholder' => 'Enter tournament location'],
            ])

            // Tournament Start Date
            ->add('tournamentStartDate', null, [
                'label' => 'Start Date',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Start date cannot be blank.']),
                    new Assert\GreaterThanOrEqual([
                        'value' => (new \DateTime('today'))->format('Y-m-d'),
                        'message' => 'Start date cannot be prior to today.',
                    ]),
                ],
                'attr' => ['placeholder' => 'Select start date'],
            ])

            // Tournament End Date
            ->add('tournamentEndDate', null, [
                'label' => 'End Date',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'End date cannot be blank.']),
                    new Assert\Callback(function ($endDate, $context) {
                        $form = $context->getRoot();
                        $startDate = $form->get('tournamentStartDate')->getData();
                        if ($startDate instanceof \DateTimeInterface && $endDate instanceof \DateTimeInterface) {
                            if ($endDate < $startDate) {
                                $context->buildViolation('End date cannot be prior to the start date.')
                                    ->addViolation();
                            }
                        }
                    }),
                ],
                'attr' => ['placeholder' => 'Select end date'],
            ])

            // Tournament Type of Sport
            ->add('tournamentTOS', ChoiceType::class, [
                'label' => 'Type of Sport',
                'choices' => [
                    'Basketball' => 'Basketball',
                    'Football' => 'Football',
                    'Volleyball' => 'Volleyball',
                    'Handball' => 'Handball',
                ],
                'placeholder' => 'Select a sport',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please select a type of sport.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
}
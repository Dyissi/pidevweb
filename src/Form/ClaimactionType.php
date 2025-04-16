<?php

namespace App\Form;

use App\Entity\Claim;
use App\Entity\Claimaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ClaimactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('claimActionType', ChoiceType::class, [
                'label' => 'Action Type',
                'choices' => [
                    'Suspension' => 'Suspension',
                    'Fine' => 'Fine',
                    'Warning' => 'Warning',
                    'Explulsion' => 'Explulsion',
                    'Ban' => 'Ban',
                    'No action taken' => 'No action taken',
                ],
                'attr' => ['class' => 'form-select custom-input']
            ])
            ->add('claimActionStartDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Start Date',
                'attr' => [
                    'class' => 'form-control custom-input',
                    'placeholder' => 'Select start date'
                ]
            ])
            ->add('claimActionEndDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'End Date',
                'attr' => [
                    'class' => 'form-control custom-input',
                    'placeholder' => 'Select end date'
                ]
            ])
            ->add('claimActionNotes', TextareaType::class, [
                'label' => 'Notes',
                'attr' => [
                    'class' => 'form-control custom-input',
                    'rows' => 4,
                    'placeholder' => 'Enter notes here...'
                ]
            ]);

        // 🧠 Conditionally add the 'claim' field only if it's NOT fixed
        if (!$options['claim_fixed']) {
            $builder->add('claim', EntityType::class, [
                'class' => Claim::class,
                'choice_label' => fn (Claim $claim) => $claim->getClaimDescription(),
                'label' => 'Related Claim',
                'placeholder' => 'Select a claim',
                'attr' => ['class' => 'form-select custom-input'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Claimaction::class,
            'claim_fixed' => false, // 👈 default is editable
        ]);

        $resolver->setAllowedTypes('claim_fixed', 'bool');
    }
}

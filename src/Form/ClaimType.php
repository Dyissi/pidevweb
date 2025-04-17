<?php

namespace App\Form;

use App\Entity\Claim;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;

class ClaimType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $currentYear = date('Y') - 1;
        $septemberFirst = new \DateTime("$currentYear-09-01");
        $today = new \DateTime();

        // Only add the status field if editing
        if ($isEdit) {
            $builder->add('claimStatus', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'In Review' => 'In Review',
                    'Approved' => 'Approved',
                    'Rejected' => 'Rejected'
                ],
                'attr' => ['class' => 'form-select']
            ]);
        }

        $builder
            ->add('claimDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date',
                'data' => $today,
                'attr' => [
                    'class' => 'form-control',
                    'min' => $septemberFirst->format('Y-m-d')
                ],
                'years' => [$currentYear],
                'months' => range(9, 12)
            ])
            ->add('claimCategory', ChoiceType::class, [
                'label' => 'Category',
                'choices' => [
                    'Misconduct' => 'Misconduct',
                    'Policy Violation' => 'Policy Violation',
                    'Health Issue' => 'Health Issue'
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('claimDescription', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Please provide detailed information...',
                    'minlength' => 20,
                    'maxlength' => 200
                ],
                'constraints' => [
                    new Length([
                        'min' => 20,
                        'max' => 200,
                        'minMessage' => 'Description must be at least {{ limit }} characters',
                        'maxMessage' => 'Description cannot be longer than {{ limit }} characters'
                    ])
                ]
            ])
            ->add('id_user', EntityType::class, [
                'class' => User::class,
                'choice_label' => fn(User $user) => $user->getUserFname() . ' ' . $user->getUserLname(),
                'label' => 'Submitted By',
                'disabled' => true,
                'attr' => ['class' => 'form-select']
            ])
            ->add('id_user_to_claim', EntityType::class, [
                'class' => User::class,
                'choice_label' => fn(User $user) => $user->getUserFname() . ' ' . $user->getUserLname(),
                'label' => 'Claim Against',
                'placeholder' => 'Select a user',
                'attr' => ['class' => 'form-select']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Claim::class,
            'is_edit' => false, // custom option to toggle between new/edit
        ]);
    }
}
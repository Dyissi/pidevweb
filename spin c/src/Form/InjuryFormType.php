<?php

namespace App\Form;

use App\Entity\Injury;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\File;

class InjuryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $now = new \DateTime();
        $threeMonthsAgo = (new \DateTime())->modify('-3 months');

        $builder
            ->add('injury_date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Injury Date',
                'html5' => true,
                'attr' => [
                    'max' => $now->format('Y-m-d'),
                ],
                'constraints' => [
                    new GreaterThan(['value' => $threeMonthsAgo, 'message' => 'The date must not be older than 3 months.']),
                    new LessThan(['value' => $now, 'message' => 'The date cannot be in the future.']),
                ],
                'empty_data' => $now->format('Y-m-d'), 
            ])
            ->add('injurySeverity', ChoiceType::class, [
                'choices' => [
                    'Mild' => 'Mild',
                    'Moderate' => 'Moderate',
                    'Severe' => 'Severe',
                    'Critical' => 'Critical',
                ],
                'label' => 'Injury Severity',
            ])
            ->add('injuryDescription', TextareaType::class, [
                'label' => 'Injury Description',
            ])
            ->add('injuryType', ChoiceType::class, [
                'choices' => [
                    'Sprain' => 'Sprain',
                    'Fracture' => 'Fracture',
                    'Concussion' => 'Concussion',
                    'Bruise' => 'Bruise',
                ],
                'label' => 'Injury Type',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getUserFname() . ' ' . $user->getUserLname();
                },
                'placeholder' => 'Choose a user',
                'required' => true,
            ])
            ->add('imagePath', FileType::class, [
                'label' => 'Injury Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG or PNG)',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Injury::class,
        ]);
    }
}

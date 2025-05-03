<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isNew = $options['is_new'];
        $role = $options['role'];
        $showProfileImage = $options['show_profile_image'];

        $builder
            ->add('userFname', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z]+$/',
                        'message' => 'First name must contain only letters.',
                    ]),
                ],
            ])
            ->add('userLname', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z]+$/',
                        'message' => 'Last name must contain only letters.',
                    ]),
                ],
            ])
            ->add('userEmail', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ])
            ->add('userNbr', TextType::class, [
                'label' => 'Phone Number',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/^\+216\d{8}$/',
                        'message' => 'Phone number must start with +216 followed by 8 digits.',
                    ]),
                ],
            ])
            ->add('user_pwd', PasswordType::class, [
                'label' => 'Password',
                'mapped' => false,
                'required' => $isNew,
                'constraints' => $isNew ? [
                    new Assert\NotBlank(),
                ] : [],
                'help' => $isNew ? 'Required for new users' : 'Enter a new password to change it, or leave blank to keep current',
            ]);
        if ($showProfileImage) {
            $builder->add('profileImage', FileType::class, [
                'label' => 'Profile Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '5m',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, or GIF).',
                    ]),
                ],
            ]);
        }

        if ($role === 'athlete') {
            $builder
                ->add('athleteDoB', DateType::class, [
                    'label' => 'Date of Birth',
                    'widget' => 'single_text',
                    'required' => true,
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\LessThan([
                            'value' => 'today',
                            'message' => 'Date of birth must be in the past.',
                        ]),
                    ],
                ])
                ->add('athleteGender', ChoiceType::class, [
                    'label' => 'Gender',
                    'choices' => [
                        'Male' => 'Male',
                        'Female' => 'Female',
                        'Other' => 'Other',
                    ],
                    'required' => true,
                    'constraints' => [new Assert\NotBlank()],
                ])
                ->add('athleteHeight', NumberType::class, [
                    'label' => 'Height (cm)',
                    'required' => false,
                    'constraints' => [
                        new Assert\GreaterThan([
                            'value' => 0,
                            'message' => 'Height must be a positive number.',
                        ]),
                    ],
                ])
                ->add('athleteWeight', NumberType::class, [
                    'label' => 'Weight (kg)',
                    'required' => false,
                    'constraints' => [
                        new Assert\GreaterThan([
                            'value' => 0,
                            'message' => 'Weight must be a positive number.',
                        ]),
                    ],
                ])
                ->add('isInjured', CheckboxType::class, [
                    'label' => 'Injured',
                    'required' => false,
                ]);
        } elseif ($role === 'coach') {
            $builder->add('nbTeams', NumberType::class, [
                'label' => 'Number of Teams',
                'required' => false,
                'constraints' => [
                    new Assert\GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Number of teams cannot be negative.',
                    ]),
                ],
            ]);
        } elseif ($role === 'med_staff') {
            $builder->add('medSpecialty', ChoiceType::class, [
                'label' => 'Medical Specialty',
                'choices' => [
                    'Psychology' => 'Psychology',
                    'Physical Therapist' => 'Physical Therapist',
                    'Cardiology' => 'Cardiology',
                    'Nutrition' => 'Nutrition',
                    'Fractures' => 'Fractures',
                ],
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_new' => true,
            'role' => 'athlete',
            'show_profile_image' => false,
        ]);
        $resolver->setAllowedTypes('is_new', 'bool');
        $resolver->setAllowedTypes('role', 'string');
        $resolver->setAllowedTypes('show_profile_image', 'bool');
        $resolver->setAllowedValues('role', ['athlete', 'coach', 'med_staff']);
    }
}
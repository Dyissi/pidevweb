<?php

namespace App\Form;

use App\Entity\Recoveryplan;
use App\Entity\User;
use App\Entity\Injury;
use Symfony\Component\Form\AbstractType;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;



class RecoveryplanFormType extends AbstractType
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recoveryGoal', ChoiceType::class, [
                'label' => 'Recovery Goal',
                'choices' => [
                    'Rehabilitation' => 'Rehabilitation',
                    'Pain Management' => 'Pain Management',
                    'Mobility Restoration' => 'Mobility Restoration',
                    'Strength Training' => 'Strength Training',
                ],
                'placeholder' => 'Select a goal',
                'required' => true,
            ])
            ->add('recoveryDescription', TextareaType::class, [
                'label' => 'Recovery Description',
                'required' => true,
            ])
            ->add('recoveryStartDate', DateType::class, [
                'label' => 'Start Date',
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
            ])
            ->add('recoveryEndDate', DateType::class, [
                'label' => 'End Date',
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
                'attr' => [
                    'max' => (new \DateTime('+3 months'))->format('Y-m-d'),
                    'min' => (new \DateTime('-3 months'))->format('Y-m-d'),
                ],
                'constraints' => [
                    new Assert\Callback([$this, 'validateEndDate']),
                ],
            ])
            ->add('recoveryStatus', ChoiceType::class, [
                'label' => 'Recovery Status',
                'choices' => [
                    'Ongoing' => 'Ongoing',
                    'Completed' => 'Completed',
                    'Pending' => 'Pending',
                ],
                'placeholder' => 'Choose status',
                'required' => true,
            ])
            ->add('injury', EntityType::class, [
                'class' => Injury::class,
                'choice_label' => 'injuryType',
                'placeholder' => 'Select Injury',
                'label' => 'Injury',
                'required' => true,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'query_builder' => function () {
                    return $this->userRepository->createQueryBuilder('u')
                        ->where('u.user_role = :role')
                        ->setParameter('role', 'athlete');
                },
                'choice_label' => fn(User $user) => $user->getUserFname() . ' ' . $user->getUserLname(),
                'placeholder' => 'Choose an athlete',
                'required' => true,
                'attr' => ['data-ajax-url' => '/injury/by-user'],
                'choice_value' => 'id',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recoveryplan::class,
        ]);
    }

    public function validateEndDate($value, ExecutionContextInterface $context)
    {
        $recoveryStartDate = $context->getRoot()->get('recoveryStartDate')->getData();
        
        if ($value && $recoveryStartDate) {
            $startDate = new \DateTime($recoveryStartDate->format('Y-m-d'));
            $endDate = new \DateTime($value->format('Y-m-d'));

            if ($endDate <= $startDate) {
                $context->buildViolation('The end date can not be before the start date.')
                    ->atPath('recoveryEndDate') 
                    ->addViolation();
            }
        }
    }
}

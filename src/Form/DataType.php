<?php

namespace App\Form;

use App\Entity\Data;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataType extends AbstractType
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('performanceSpeed')
            ->add('performanceAgility')
            ->add('performanceNbrGoals')
            ->add('performanceAssists')
            ->add('performanceDateRecorded', null, [
                'widget' => 'single_text',
            ])
            ->add('performanceNbrFouls')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'query_builder' => function () {
                    return $this->userRepository->createQueryBuilder('u')
                        ->where('u.user_role = :role')  // Exactly matches your DB column
                        ->setParameter('role', 'athlete'); // Exact value from database
                },
                'choice_label' => fn(User $user) => $user->getUserFname() . ' ' . $user->getUserLname(),
                'placeholder' => 'Select an athlete',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Data::class,
        ]);
    }
}
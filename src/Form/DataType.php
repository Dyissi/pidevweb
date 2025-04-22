<?php

namespace App\Form;

use App\Entity\Data;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // Use the correct alias
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataType extends AbstractType
{
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
            ->add('user', EntityType::class, [ // Correctly reference the EntityType
                'class' => User::class,
                'choice_label' => 'fullName', // Or any other property to display
                'placeholder' => 'Select a user',
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

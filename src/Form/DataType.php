<?php

namespace App\Form;

use App\Entity\Data;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
            ->add('userId')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Data::class,
        ]);
    }
}

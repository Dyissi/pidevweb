<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ChartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Bar Chart' => 'bar',
                    'Line Chart' => 'line',
                    'Pie Chart' => 'pie'
                ]
            ])
            ->add('timeframe', ChoiceType::class, [
                'choices' => [
                    'Weekly' => 'week',
                    'Monthly' => 'month'
                ]
            ]);
    }
}
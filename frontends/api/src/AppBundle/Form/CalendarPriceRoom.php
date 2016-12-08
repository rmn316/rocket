<?php

namespace AppBundle\Form;

use AppBundle\Entity\CalendarPriceRoom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalendarPriceRoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'room'

        )->add(
            'start_at',
            DateType::class,
            [
                'required' => true
            ]
        )->add(
            'end_at',
            DateType::class,
            [
                'required' => true
            ]
        )->add(
            'price',
            NumberType::class,
            [
                'required' => true
            ]
        )->add(
            'days',
            ChoiceType::class,
            [
                'choices' => [
                    'MO' => 'Mondays',
                    'TU' => 'Tuesdays',
                    'WE' => 'Wednesdays',
                    'TH' => 'Thursdays',
                    'FR' => 'Fridays',
                    'SA' => 'Saturdays',
                    'SU' => 'Sundays'
                ],
                'mapped' => false
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => CalendarPriceRoom::class]);
    }

    public function getBlockPrefix()
    {
        return 'calendar_price_room';
    }

}

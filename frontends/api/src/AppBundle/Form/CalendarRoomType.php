<?php

namespace AppBundle\Form;

use AppBundle\Entity\CalendarPriceRoom;
use AppBundle\Entity\CalendarRoom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CalendarRoomType
 * @package AppBundle\Form
 */
class CalendarRoomType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'room',
            RoomType::class,
            [
                'required' => true
            ]
        )->add(
            'start_date',
            DateType::class,
            [
                'required' => true,
                'property_path' => 'startAt',
                'widget' => 'single_text'
            ]
        )->add(
            'end_date',
            DateType::class,
            [
                'required' => true,
                'property_path' => 'endAt',
                'widget' => 'single_text'
            ]
        )->add(
            'price',
            NumberType::class,
            [
                'required' => true
            ]
        )->add(
            'inventory',
            IntegerType::class
        )->add(
            'days',
            ChoiceType::class,
            [
                'choices' => [
                    'All Days' => 'ALL',
                    'Weekdays' => 'WEEKDAY',
                    'Weekends' => 'WEEKEND',
                    'Mondays' => 'MO',
                    'Tuesdays' => 'TU',
                    'Wednesdays' => 'WE',
                    'Thursdays' => 'TH',
                    'Fridays' => 'FR',
                    'Saturdays' => 'SA',
                    'Sundays' => 'SU'
                ],
                'multiple' => true
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => CalendarRoom::class,
                'validation_groups' => ['CalendarRoom']
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'calendar_room';
    }
}

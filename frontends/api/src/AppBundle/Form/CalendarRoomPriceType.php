<?php

namespace AppBundle\Form;

use AppBundle\Entity\CalendarRoom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalendarRoomPriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'room',
            RoomType::class,
            [
                'required' => true
            ]
        )->add(
            'date',
            DateType::class,
            [
                'required' => true,
                'property_path' => 'dateAt',
                'widget' => 'single_text',
            ]
        )->add(
            'price',
            NumberType::class,
            [
                'required' => true
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => CalendarRoom::class,
                'validation_groups' => ['PriceIndividual']
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'calendar_room_price';
    }

}
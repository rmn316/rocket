<?php

namespace AppBundle\Form;

use AppBundle\Entity\CalendarRoom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CalendarRoomInventoryType
 * @package AppBundle\Form
 */
class CalendarRoomInventoryType extends AbstractType
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
            'date',
            DateType::class,
            [
                'required' => true,
                'property_path' => 'dateAt',
                'widget' => 'single_text'
            ]
        )->add(
            'inventory',
            IntegerType::class,
            [
                'required' => true
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
                'validation_groups' => ['PriceIndividual']
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'calendar_room_inventory';
    }

}
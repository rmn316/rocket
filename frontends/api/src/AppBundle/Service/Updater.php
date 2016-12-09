<?php

namespace AppBundle\Service;

use AppBundle\Repository\CalendarRoomRepository;
use AppBundle\Repository\RoomRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;

interface Updater
{
    public function __construct(
        EntityManager $entityManager,
        CalendarRoomRepository $calendarRoomRepository,
        RoomRepository $roomRepository
    );

    public function update(Form $form);
}

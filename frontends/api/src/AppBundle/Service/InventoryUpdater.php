<?php

namespace AppBundle\Service;

use AppBundle\Entity\CalendarInventoryRoom;
use AppBundle\Entity\CalendarRoom;
use AppBundle\Entity\Room;
use AppBundle\Exception\BadRequestException;
use AppBundle\Repository\CalendarRoomRepository;
use AppBundle\Repository\RoomRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Recurr\Rule;
use Symfony\Component\Form\Form;

class InventoryUpdater implements Updater
{
    /** @var CalendarRoomRepository */
    private $calendarRoomRepository;
    /** @var RoomRepository */
    private $roomRepository;
    /** @var EntityManager */
    private $entityManager;

    /**
     * InventoryUpdater constructor.
     * @param EntityManager $entityManager
     * @param CalendarRoomRepository $calendarRoomRepository
     * @param RoomRepository $roomRepository
     */
    public function __construct(
        EntityManager $entityManager,
        CalendarRoomRepository $calendarRoomRepository,
        RoomRepository $roomRepository
    ) {
        $this->calendarRoomRepository = $calendarRoomRepository;
        $this->roomRepository = $roomRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Form $form
     * @return bool
     */
    public function update(Form $form)
    {
        $this->validate($form);

        /** @var CalendarRoom $calendarRoom */
        $calendarRoom = $form->getData();

        $room = $this->roomRepository->findOneBy(['key' => $calendarRoom->getRoom()->getKey()]);
        /** @var CalendarRoom $existingCalendarRoom */
        $existingCalendarRoom = $this->calendarRoomRepository->findOneBy(
            ['dateAt' => $calendarRoom->getDateAt(), 'room' => $room]
        );

        if ($existingCalendarRoom !== null) {
            $existingCalendarRoom->setInventory($calendarRoom->getInventory());
            $this->entityManager->persist($existingCalendarRoom);
            $this->entityManager->flush($existingCalendarRoom);
        } else {
            $calendarRoom->setRoom($room);
            $this->entityManager->persist($calendarRoom);
            $this->entityManager->flush($calendarRoom);
        }
        return true;
    }

    /**
     * @param Form $form
     * @throws BadRequestException
     */
    private function validate(Form $form)
    {
        if (!$form->isValid()) {
            throw new BadRequestException($form->getErrors());
        }
    }
}

<?php

namespace AppBundle\Service;

use AppBundle\Entity\CalendarRoom;
use AppBundle\Entity\Room;
use AppBundle\Exception\BadRequestException;
use AppBundle\Repository\CalendarRoomRepository;
use AppBundle\Repository\RoomRepository;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;

class CalendarUpdater implements Updater
{
    /**
     * @var CalendarRoomRepository
     */
    private $calendarRoomRepository;
    /**
     * @var RoomRepository
     */
    private $roomRepository;
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager, CalendarRoomRepository $calendarRoomRepository, RoomRepository $roomRepository)
    {
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

        /** @var CalendarRoom $formData */
        $formData = $form->getData();

        /** @var Room $room */
        $room = $this->roomRepository->findOneBy(['key' => $formData->getRoom()->getKey()]);
        $formData->setRoom($room);

        $existingCalendar = $this->calendarRoomRepository->findByStartAndEndDateAndRoom(
            $formData->getStartAt(),
            $formData->getEndAt(),
            $room
        );

        $this->persistCalendar($formData, $existingCalendar);

        return true;
    }


    /**
     * @param CalendarRoom $formData
     * @param ArrayCollection $calendarRooms
     * @internal param Room $room
     */
    private function persistCalendar(CalendarRoom $formData, ArrayCollection $calendarRooms)
    {
        $inclusiveEndDate = clone $formData->getEndAt();
        $inclusiveEndDate->modify('+1 DAY');

        $period = new DatePeriod($formData->getStartAt(), new DateInterval('P1D'), $inclusiveEndDate);
        foreach ($period as $day) {
            if ($this->guardAgainstExcludedDate($formData, $day)) {
                continue;
            }

            $calendarRoom = $this->populateCalendarRoom($day, $formData, $calendarRooms);
            $this->entityManager->persist($calendarRoom);
            $this->entityManager->flush($calendarRoom);
        }
    }

    private function populateCalendarRoom(DateTime $day, CalendarRoom $formData, ArrayCollection $collection)
    {
        /** @var CalendarRoom[] $calendarRoom */
        $calendarRoom = $collection->filter(function($calendarRoom) use ($day, $formData){
            return $calendarRoom->getStartAt() == $day && $calendarRoom->getRoom() == $formData->getRoom()->getKey();
        });

        if ($calendarRoom[0] instanceof CalendarRoom && $calendarRoom[0]->getId() > 0) {
            $calendarRoom = $calendarRoom[0];
        } else {
            $calendarRoom = $formData;
            $calendarRoom->setDateAt($day);

            $this->getDefaultInventory($calendarRoom);
            $calendarRoom->setPrice($calendarRoom->getPrice() > 0 ? $calendarRoom->getPrice() : 0);
        }

        return $calendarRoom;
    }

    private function getDefaultInventory(CalendarRoom $calendarRoom)
    {
        if (!$calendarRoom->getInventory() > 0) {
            switch ($calendarRoom->getRoom()->getKey()) {
                case Room::DOUBLE:
                    $calendarRoom->setInventory(CalendarRoom::DEFAULT_INVENTORY_DOUBLE);
                    break;
                case Room::SINGLE:
                    $calendarRoom->setInventory(CalendarRoom::DEFAULT_INVENTORY_SINGLE);
                    break;
            }
        }
    }

    private function validate(Form $form)
    {
        if (!$form->isValid()) {
            throw new BadRequestException($form->getErrors());
        }
    }

    private function guardAgainstExcludedDate(CalendarRoom $calendarRoom, DateTime $date)
    {
        $daysFilter = array_search($date->format('w'), $calendarRoom->getFilterDays());
        if (in_array($daysFilter, $this->processDaysRestriction($calendarRoom->getDays()))) {
            return true;
        } else {
            return false;
        }
    }

    private function processDaysRestriction(array $days)
    {
        $restriction = [];
        foreach ($days as $key => $value) {
            switch (strtoupper($value)) {
                case 'WEEKEND':
                    $restriction = ['SA', 'SU'];
                    unset($days[$key]);
                    break;
                case 'WEEKDAY':
                    $restriction = ['MO','TU','WE','TH','FR'];
                    unset($days[$key]);
                    break;
                case 'ALL':
                    unset($days[$key]);
                    break;
            }
        }
        return array_replace($days, $restriction);
    }
}
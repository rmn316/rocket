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
//            $dateObj = clone $day;
            if ($this->guardAgainstExcludedDate($formData, $day)) {
                continue;
            }

            $this->persistCalendarRoom($day, $formData, $calendarRooms);
        }
    }

    private function persistCalendarRoom(DateTime $day, CalendarRoom $formData, ArrayCollection $collection)
    {
        /** @var ArrayCollection $calendarRooms */
        $calendarRooms = $collection->filter(function($calendarRoom) use ($day, $formData){
            $condition1 = $calendarRoom->getDateAt() == $day;
            $condition2 = $calendarRoom->getRoom()->getKey() === $formData->getRoom()->getKey();
            return $condition1 && $condition2;
        });

        if (count($calendarRooms) > 0) {
            /** @var CalendarRoom $calendarRoom */
            $calendarRoom = $calendarRooms->first();
            $calendarRoom->setInventory($this->getDefaultInventory($formData));
            $calendarRoom->setPrice($formData->getPrice() > 0 ? $formData->getPrice() : $calendarRoom->getPrice());
        } else {
            $calendarRoom = new CalendarRoom();
            $calendarRoom->setRoom($formData->getRoom());
            $calendarRoom->setInventory($this->getDefaultInventory($formData));
            $calendarRoom->setPrice($formData->getPrice());
            $calendarRoom->setDateAt($day);
        }

        $this->entityManager->persist($calendarRoom);
        $this->entityManager->flush($calendarRoom);

        return $calendarRoom;
    }

    /**
     * @param CalendarRoom $calendarRoom
     * @return int|null
     */
    private function getDefaultInventory(CalendarRoom $calendarRoom)
    {
        if (!$calendarRoom->getInventory() > 0) {
            $value = null;
            switch ($calendarRoom->getRoom()->getKey()) {
                case Room::DOUBLE:
                    $value = CalendarRoom::DEFAULT_INVENTORY_DOUBLE;
                    break;
                case Room::SINGLE:
                    $value = CalendarRoom::DEFAULT_INVENTORY_SINGLE;
                    break;
            }
            return $value;
        } else {
            return $calendarRoom->getInventory();
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
        $days = $this->updateDaysRestriction($calendarRoom->getDays());
        $daysFilter = array_search($date->format('w'), $calendarRoom->getFilterDays());

        if (count($days) === 0) {
            return false;
        } elseif (in_array($daysFilter, $days)) {
            return false;
        } else {
            return true;
        }
    }

    private function updateDaysRestriction(array $days)
    {
        $restriction = $output = [];
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
                    $restriction = ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'];
                    unset($days[$key]);
                    break;
            }
            $output = array_merge($days, $restriction);
        }
        return $output;
    }
}
<?php

namespace AppBundle\Service;

use AppBundle\Entity\CalendarRoom;
use AppBundle\Entity\Room;
use AppBundle\Repository\CalendarRoomRepository;
use AppBundle\Repository\RoomRepository;
use DateInterval;
use DatePeriod;
use DateTime;

class CalendarBuilder
{
    protected $output = [];
    /** @var CalendarRoomRepository */
    private $calendarRoomRepository;
    /**
     * @var RoomRepository
     */
    private $roomRepository;

    public function __construct(CalendarRoomRepository $calendarRoomRepository, RoomRepository $roomRepository)
    {
        $this->calendarRoomRepository = $calendarRoomRepository;
        $this->roomRepository = $roomRepository;
    }

    public function build(DateTime $startObj, DateTime $endObj)
    {
        $this->buildEmptyResultSet($startObj, $endObj);
        $calendarRooms = $this->calendarRoomRepository->findByStartAndEndDate($startObj, $endObj);

        foreach ($calendarRooms as $calendarRoom) {
            $room = $calendarRoom->getRoom();
            $formattedDate = $calendarRoom->getDateAt()->format('Y-m-d');
            $this->output[$room->getKey()][$formattedDate] = $this->dataSet($calendarRoom);
        }
        return $this->output;
    }

    /**
     * @param DateTime $startObj
     * @param DateTime $endObj
     * @return array
     */
    private function buildEmptyDates(DateTime $startObj, DateTime $endObj, Room $room)
    {
        $dates = [];
        $inclusiveEndDate = clone $endObj;
        $inclusiveEndDate->modify('+1 DAY');

        /** @var DateTime[] $period */
        $period = new DatePeriod($startObj, new DateInterval('P1D'), $inclusiveEndDate);
        foreach ($period as $day) {
            $dates[$day->format('Y-m-d')] = [
                'room' => [
                    'key' => $room->getKey()
                ],
                'date' => $day->format('Y-m-d'),
                'price' => 0,
                'inventory' => 0
            ];
        }
        return $dates;
    }

    /**
     * @param DateTime $startObj
     * @param DateTime $endObj
     * @param null $roomType
     * @internal param Room[] $rooms
     */
    private function buildEmptyResultSet(DateTime $startObj, DateTime $endObj, $roomType = null)
    {
        $rooms = $this->roomRepository->findAll();
        foreach ($rooms as $room) {
            if ($roomType === null || $roomType === $room->getKey()) {
                $this->output[$room->getKey()] = $this->buildEmptyDates($startObj, $endObj, $room);
            }
        }
    }

    private function dataSet(CalendarRoom $calendarRoom)
    {
        return [
            'room' => [
                'key' => $calendarRoom->getRoom()->getKey()
            ],
            'date' => $calendarRoom->getDateAt(),
            'price' => $calendarRoom->getPrice(),
            'inventory' => $calendarRoom->getInventory()
        ];
    }
}

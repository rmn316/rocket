<?php

namespace AppBundle\Service;

use AppBundle\Entity\CalendarInventoryRoom;
use AppBundle\Entity\CalendarPriceRoom;
use AppBundle\Entity\Room;
use AppBundle\Repository\CalendarInventoryRoomRepository;
use AppBundle\Repository\CalendarPriceRoomRepository;
use AppBundle\Repository\RoomRepository;
use DateTime;
use Exception;
use Recurr\Recurrence;
use Recurr\RecurrenceCollection;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;

class InventoryBuilder extends Builder
{
    /** @var CalendarInventoryRoomRepository */
    private $calendarInventoryRoomRepository;

    public function __construct(CalendarInventoryRoomRepository $calendarInventoryRoomRepository) {
        $this->calendarInventoryRoomRepository = $calendarInventoryRoomRepository;
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param null $roomType
     * @return array
     * @throws Exception
     */
    public function build(DateTime $startDate, DateTime $endDate, $roomType = null)
    {
        if (empty($this->output)) {
            throw new Exception('Not Initialised');
        }

        /** @var CalendarInventoryRoom[] $calendarInventoryRooms */
        $calendarInventoryRooms = $this->calendarInventoryRoomRepository->findByStartAndEndDate($startDate, $endDate);
        foreach ($calendarInventoryRooms as $calendarRoom) {
            $rule = new Rule($calendarRoom->getRule(), $calendarRoom->getStartAt(), $calendarRoom->getEndAt());
            $this->generate($startDate, $endDate, $calendarRoom, $this->getTransformer()->transform($rule));
        }

        return $this->output;
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param CalendarInventoryRoom $calendarRoom
     * @param RecurrenceCollection $dates
     * @return array
     */
    private function generate(DateTime $startDate, DateTime $endDate, CalendarInventoryRoom $calendarRoom, RecurrenceCollection $dates)
    {
        $modifiedEndDate = clone $endDate;
        $modifiedEndDate->modify('+1 DAY');

        $roomKey = $calendarRoom->getRoom()->getKey();

        $rule = new Rule($calendarRoom->getRule());
        $daysOnly = $rule->getByDay();

        /** @var Recurrence[] $arrayOfDates */
        $arrayOfDates = $dates->toArray();

        /** @var DateTime[] $period */
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $modifiedEndDate);
        foreach ($period as $day) {
            $formattedDate = $day->format('Y-m-d');

            if ($this->isValid($day, $arrayOfDates, $daysOnly)) {
                if (array_key_exists($formattedDate, $this->output[$calendarRoom->getRoom()->getKey()])) {
                    // date is set check if new add apply.
                    if ($this->output[$roomKey][$formattedDate]['created'] < $calendarRoom->getCreatedAt()) {
                        $this->output[$roomKey][$formattedDate] = $this->buildData($calendarRoom, $day);
                    }
                } else {
                    $this->output[$roomKey][$formattedDate] = $this->buildData($calendarRoom, $day);
                }
            }
        }
    }

    /**
     * @param CalendarInventoryRoom $calendarRoom
     * @param DateTime $date
     * @return array
     */
    private function buildData(CalendarInventoryRoom $calendarRoom, DateTime $date)
    {
        $room = $calendarRoom->getRoom();
        return [
            'date' => $date,
            'inventory' => $calendarRoom->getInventory(),
            'room' => [
                'name' => $room->getName(),
                'key' => $room->getKey()
            ],
            'created' => $calendarRoom->getCreatedAt()
        ];
    }
}
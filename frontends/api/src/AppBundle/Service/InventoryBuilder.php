<?php

namespace AppBundle\Service;

use AppBundle\Entity\CalendarInventoryRoom;
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

class InventoryBuilder
{
    /** @var CalendarInventoryRoomRepository */
    private $calendarInventoryRoomRepository;
    /** @var array */
    private $output = [];

    public function __construct(CalendarInventoryRoomRepository $calendarInventoryRoomRepository) {
        $this->calendarInventoryRoomRepository = $calendarInventoryRoomRepository;
    }

    /**
     * @param Room[] $rooms
     * @param null $roomType
     */
    public function buildEmptyResultSet(array $rooms, $roomType = null)
    {
        foreach ($rooms as $room) {
            if ($roomType === null || $roomType === $room->getKey()) {
                $this->output[$room->getKey()] = [];
            }
        }
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

        // get all rules.
        $calendarInventoryRooms = $this->calendarInventoryRoomRepository->findByStartAndEndDate($startDate, $endDate);

        $transformer = new ArrayTransformer();
        $transformerConfig = new ArrayTransformerConfig();
        $transformerConfig->enableLastDayOfMonthFix();
        $transformer->setConfig($transformerConfig);

        foreach ($calendarInventoryRooms as $calendarRoom) {
            $rule = new Rule($calendarRoom->getRule(), $calendarRoom->getStartAt(), $calendarRoom->getEndAt());
            $this->generate($startDate, $endDate, $calendarRoom, $transformer->transform($rule));
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
        $roomKey = $calendarRoom->getRoom()->getKey();

        /** @var Recurrence[] $arrayOfDates */
        $arrayOfDates = $dates->toArray();

        /** @var DateTime[] $period */
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);
        foreach ($period as $day) {
            $formattedDate = $day->format('Y-m-d');

            $isValid = false;
            foreach ($arrayOfDates as $value) {
                if ($value->getStart() <= $day && $value->getEnd() >= $day) {
                    // valid
                    $isValid = true;
                    break;
                }
            }

            if ($isValid) {
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
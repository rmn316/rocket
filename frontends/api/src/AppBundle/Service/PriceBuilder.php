<?php

namespace AppBundle\Service;

use AppBundle\Entity\CalendarPriceRoom;
use AppBundle\Entity\Room;
use AppBundle\Repository\CalendarPriceRoomRepository;
use DateTime;
use Exception;
use Recurr\Recurrence;
use Recurr\RecurrenceCollection;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;

class PriceBuilder
{
    /** @var CalendarPriceRoomRepository */
    private $calendarPriceRoomRepository;
    /** @var array */
    private $output = [];

    public function __construct(CalendarPriceRoomRepository $calendarPriceRoomRepository)
    {
        $this->calendarPriceRoomRepository = $calendarPriceRoomRepository;
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

    public function build(DateTime $startDate, DateTime $endDate, $roomType)
    {
        if (empty($this->output)) {
            throw new Exception('Not Initialised');
        }

        $calendarPriceRooms = $this->calendarPriceRoomRepository->findByStartAndEndDate($startDate, $endDate);

        $transformer = new ArrayTransformer();
        $transformerConfig = new ArrayTransformerConfig();
        $transformerConfig->enableLastDayOfMonthFix();
        $transformer->setConfig($transformerConfig);

        foreach ($calendarPriceRooms as $calendarRoom) {
            $rule = new Rule($calendarRoom->getRule(), $calendarRoom->getStartAt(), $calendarRoom->getEndAt());
            $this->generate($startDate, $endDate, $calendarRoom, $transformer->transform($rule));
        }

        return $this->output;
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param CalendarPriceRoom $calendarRoom
     * @param RecurrenceCollection $dates
     * @return array
     */
    private function generate(DateTime $startDate, DateTime $endDate, CalendarPriceRoom $calendarRoom, RecurrenceCollection $dates)
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
     * @param CalendarPriceRoom $calendarRoom
     * @param DateTime $date
     * @return array
     */
    private function buildData(CalendarPriceRoom $calendarRoom, DateTime $date)
    {
        $room = $calendarRoom->getRoom();
        return [
            'date' => $date,
            'price' => $calendarRoom->getPrice(),
            'room' => [
                'name' => $room->getName(),
                'key' => $room->getKey()
            ],
            'created' => $calendarRoom->getCreatedAt()
        ];
    }
}
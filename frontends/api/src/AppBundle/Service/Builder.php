<?php

namespace AppBundle\Service;

use AppBundle\Entity\Room;
use DateTime;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;

abstract class Builder
{
    protected $output = [];
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

    protected function isValid(DateTime $day, array $arrayOfDates, $specificDays = [])
    {
        if (count($specificDays) > 0 && !in_array(substr(strtoupper($day->format('l')), 0, 2), $specificDays)) {
            return false;
        }

        $isValid = false;
        foreach ($arrayOfDates as $value) {
            if ($value->getStart() <= $day && $value->getEnd() >= $day) {
                // valid
                $isValid = true;
                break;
            }
        }

        return $isValid;
    }

    protected function getTransformer()
    {
        $transformer = new ArrayTransformer();
        $transformerConfig = new ArrayTransformerConfig();
        $transformerConfig->enableLastDayOfMonthFix();
        $transformer->setConfig($transformerConfig);

        return $transformer;
    }
}
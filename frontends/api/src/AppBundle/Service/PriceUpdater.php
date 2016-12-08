<?php

namespace AppBundle\Service;

use AppBundle\Entity\CalendarPriceRoom;
use AppBundle\Entity\Room;
use AppBundle\Exception\BadRequestException;
use DateTime;
use Recurr\Rule;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class PriceUpdater extends Updater
{
    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param string $price
     * @param string $roomType
     * @param array $days
     * @return bool
     */
    public function update(DateTime $startDate, DateTime $endDate, $price, $roomType, array $days = [])
    {
        $rule = $this->buildRule($startDate, $endDate, $days);

        $rooms = $this->getRooms($roomType);
        foreach ($rooms as $room) {
            $this->getEntityForPersist($startDate, $endDate, $rule, $room, $price);
        }
        return true;
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param Rule $rule
     * @param Room $room
     * @param $value
     * @return CalendarPriceRoom
     */
    protected function getEntityForPersist(DateTime $startDate, DateTime $endDate, Rule $rule, Room $room, $value)
    {
        $repository = $this->entityManager->getRepository(CalendarPriceRoom::class);
        $calendarPriceRoom = $repository->findOneBy(
            ['startAt' => $startDate, 'endAt' => $endDate, 'rule' => $rule->getString(), 'room' => $room]
        );
        if ($calendarPriceRoom === null) {
            $calendarPriceRoom = new CalendarPriceRoom();
            $calendarPriceRoom->setCreatedAt(new DateTime());
        }

        $calendarPriceRoom->setPrice($value);
        $calendarPriceRoom->setRoom($room);
        $calendarPriceRoom->setRule($rule->getString());
        $calendarPriceRoom->setStartAt($startDate);
        $calendarPriceRoom->setEndAt($endDate);
        $this->entityManager->persist($calendarPriceRoom);
        $this->entityManager->flush($calendarPriceRoom);

        return $calendarPriceRoom;
    }
}
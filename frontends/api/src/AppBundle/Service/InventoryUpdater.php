<?php

namespace AppBundle\Service;

use AppBundle\Entity\CalendarInventoryRoom;
use AppBundle\Entity\Room;
use AppBundle\Repository\RoomRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Recurr\Rule;

class InventoryUpdater extends Updater
{
    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param $inventory
     * @param array $parameters
     * @return bool
     */
    public function update(DateTime $startDate, DateTime $endDate, $inventory, array $parameters = [])
    {
        $rule = $this->buildRule($startDate, $endDate, $parameters);

        $rooms = $this->getRooms($parameters['room']);
        foreach ($rooms as $room) {
            $this->getEntityForPersist($startDate, $endDate, $rule, $room, $inventory);
        }
        return true;
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param Rule $rule
     * @param Room $room
     * @param $value
     * @return CalendarInventoryRoom
     */
    protected function getEntityForPersist(DateTime $startDate, DateTime $endDate, Rule $rule, Room $room, $value)
    {
        $repository = $this->entityManager->getRepository(CalendarInventoryRoom::class);
        $calendarInventoryRoom = $repository->findOneBy(
            ['startAt' => $startDate, 'endAt' => $endDate, 'rule' => $rule->getString(), 'room' => $room]
        );
        if ($calendarInventoryRoom === null) {
            $calendarInventoryRoom = new CalendarInventoryRoom();
            $calendarInventoryRoom->setCreatedAt(new DateTime());
        }

        $calendarInventoryRoom->setInventory($value);
        $calendarInventoryRoom->setRoom($room);
        $calendarInventoryRoom->setRule($rule->getString());
        $calendarInventoryRoom->setStartAt($startDate);
        $calendarInventoryRoom->setEndAt($endDate);
        $this->entityManager->persist($calendarInventoryRoom);
        $this->entityManager->flush($calendarInventoryRoom);

        return $calendarInventoryRoom;
    }
}

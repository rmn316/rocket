<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CalendarRoom;
use AppBundle\Entity\Room;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use DateTime;
use Doctrine\ORM\NoResultException;

class CalendarRoomRepository extends EntityRepository
{
    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return CalendarRoom[]
     * @throws NoResultException
     */
    public function findByStartAndEndDate(DateTime $startDate, DateTime $endDate)
    {
        $createBuilder = $this->createQueryBuilder('c')
            ->where('c.dateAt >= :startDate AND c.dateAt <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery();

        try {
            $result = $createBuilder->getResult();
        } catch (NoResultException $e) {
            throw $e;
        }
        return $result;
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param Room $room
     * @return ArrayCollection
     * @throws NoResultException
     */
    public function findByStartAndEndDateAndRoom(DateTime $startDate, DateTime $endDate, Room $room)
    {
        $createBuilder = $this->createQueryBuilder('c')
            ->where('c.dateAt >= :startDate AND c.dateAt <= :endDate AND c.room = :room')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('room', $room)
            ->getQuery();

        try {
            $result = $createBuilder->getResult();
        } catch (NoResultException $e) {
            throw $e;
        }
        return new ArrayCollection($result);
    }
}

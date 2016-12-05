<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CalendarInventoryRoom;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class CalendarInventoryRoomRepository extends EntityRepository
{
    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return CalendarInventoryRoom[]
     * @throws NoResultException
     */
    public function findByStartAndEndDate(DateTime $startDate, DateTime $endDate)
    {
        // TODO possibly a bit of a mess.
        $createBuilder = $this->createQueryBuilder('c')
            ->where('c.startAt <= :startDate AND c.endAt >= :endDate')
            ->orWhere('c.startAt >= :startDate AND c.endAt >= :endDate')
            ->orWhere('c.startAt >= :startDate AND c.endAt <= :endDate')
            ->orWhere('c.startAt <= :startDate AND c.endAt <= :endDate')
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
}
<?php

namespace AppBundle\Service;

use AppBundle\Entity\Room;
use AppBundle\Repository\RoomRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Recurr\Rule;

abstract class Updater
{
    const FREQUENCY = 'DAILY';
    const TIMEZONE = 'Europe/London';

    /** @var EntityManager */
    protected $entityManager;
    /** @var RoomRepository */
    protected $roomRepository;

    public function __construct(EntityManager $entityManager, RoomRepository $roomRepository)
    {
        $this->entityManager = $entityManager;
        $this->roomRepository = $roomRepository;
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    abstract public function update(DateTime $startDate, DateTime $endDate, $value, array $parameters = []);

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param Rule $rule
     * @param Room $room
     * @param $value
     * @return mixed
     */
    abstract protected function getEntityForPersist(
        DateTime $startDate,
        DateTime $endDate,
        Rule $rule,
        Room $room,
        $value
    );

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param array $parameters
     * @return Rule
     */
    protected function buildRule(DateTime $startDate, DateTime $endDate, array $parameters = [])
    {
        $rule = (new Rule)
            ->setStartDate($startDate)
            ->setTimezone(self::TIMEZONE)
            ->setFreq(self::FREQUENCY)
            ->setUntil($endDate);

        if ($parameters['days']) {
            $rule->setByDay($this->processDaysRestriction($parameters['days']));
        }

        return $rule;
    }

    /**
     * @param string|null $roomKey
     * @return Room[]
     */
    protected function getRooms($roomKey = null)
    {
        $response = [];
        $rooms = $this->roomRepository->findAll();
        foreach ($rooms as $room) {
            if ($roomKey === null || $roomKey === $room->getKey()) {
                $response[] = $room;
            }
        }
        return $response;
    }

    /**
     * @param array $days
     * @return array
     */
    protected function processDaysRestriction(array $days)
    {
        $restriction = [];
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
                    unset($days[$key]);
                    break;
            }
        }
        return array_replace($days, $restriction);
    }
}
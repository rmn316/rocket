<?php

namespace AppBundle\Service;

use AppBundle\Entity\Room;
use AppBundle\Exception\BadRequestException;
use AppBundle\Repository\RoomRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Recurr\Rule;
use Symfony\Component\Form\Form;

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
     * @param string $room
     * @param array $days
     * @return bool
     */
    abstract public function update(DateTime $startDate, DateTime $endDate, $value, $room, array $days = []);

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
     * @param Form $form
     * @throws BadRequestException
     */
    protected function validateForm(Form $form)
    {
        if (!$form->isValid()) {
            throw new BadRequestException($form->getErrors());
        }
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param array $days
     * @return Rule
     */
    protected function buildRule(DateTime $startDate, DateTime $endDate, array $days = [])
    {
        $rule = (new Rule)
            ->setStartDate($startDate)
            ->setTimezone(self::TIMEZONE)
            ->setFreq(self::FREQUENCY)
            ->setUntil($endDate);

        if (count($days) > 0) {
            $rule->setByDay($this->processDaysRestriction($days));
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
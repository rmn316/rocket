<?php

namespace AppBundle\Entity;

use DateTime;

class CalendarRoom
{
    const DEFAULT_INVENTORY_DOUBLE = 5;
    const DEFAULT_INVENTORY_SINGLE = 5;

    /** @var integer */
    private $id;
    /** @var Room */
    private $room;
    /** @var DateTime */
    private $dateAt;
    /** @var float */
    private $price = 0;
    /** @var integer */
    private $inventory = 0;
    /** @var DateTime */
    private $startAt;
    /** @var DateTime */
    private $endAt;
    /** @var array */
    private $days = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Room
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param Room $room
     */
    public function setRoom(Room $room)
    {
        $this->room = $room;
    }

    /**
     * @return DateTime
     */
    public function getDateAt()
    {
        return $this->dateAt;
    }

    /**
     * @param DateTime $dateAt
     */
    public function setDateAt($dateAt)
    {
        $this->dateAt = $dateAt;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getInventory()
    {
        return $this->inventory;
    }

    /**
     * @param int $inventory
     */
    public function setInventory($inventory)
    {
        $this->inventory = $inventory;
    }

    public function getFilterDays()
    {
        return [
            'SU' => 0,
            'MO' => 1,
            'TU' => 2,
            'WE' => 3,
            'TH' => 4,
            'FR' => 5,
            'SA' => 6
        ];
    }

    /* ================= BULK FORM HELPER FUNCTIONS ================= */

    /**
     * @return DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * @param DateTime $startAt
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;
    }

    /**
     * @return DateTime
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * @param DateTime $endAt
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;
    }

    /**
     * @return array
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @param array $days
     */
    public function setDays($days)
    {
        $this->days = $days;
    }
}

<?php

namespace AppBundle\Entity;

use DateTime;

class CalendarPriceRoom
{
    /** @var integer */
    private $id;
    /** @var Room */
    private $room;
    /** @var DateTime */
    private $startAt;
    /** @var DateTime */
    private $endAt;
    /** @var string */
    private $rule;
    /** @var float */
    private $price;
    /** @var DateTime */
    private $createdAt;

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
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * @param DateTime $startAt
     */
    public function setStartAt(DateTime $startAt)
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
    public function setEndAt(DateTime $endAt)
    {
        $this->endAt = $endAt;
    }

    /**
     * @return string
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @param string $rule
     */
    public function setRule($rule)
    {
        $this->rule = $rule;
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
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
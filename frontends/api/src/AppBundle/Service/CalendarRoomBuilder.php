<?php

namespace AppBundle\Service;

use AppBundle\Repository\RoomRepository;
use DateTime;

class CalendarRoomBuilder
{
    /** @var PriceBuilder */
    private $priceBuilder;
    /** @var InventoryBuilder */
    private $inventoryBuilder;
    /** @var RoomRepository */
    private $roomRepository;

    public function __construct(PriceBuilder $priceBuilder, InventoryBuilder $inventoryBuilder, RoomRepository $roomRepository)
    {
        $this->priceBuilder = $priceBuilder;
        $this->inventoryBuilder = $inventoryBuilder;
        $this->roomRepository = $roomRepository;
    }

    public function build(DateTime $startDate, DateTime $endDate, $roomType = null)
    {
        $rooms = $this->roomRepository->findAll();
        $this->priceBuilder->buildEmptyResultSet($rooms, $roomType);
        $price = $this->priceBuilder->build($startDate, $endDate, $roomType);
        $this->inventoryBuilder->buildEmptyResultSet($rooms, $roomType);
        $inventory = $this->inventoryBuilder->build($startDate, $endDate, $roomType);

        return array_replace_recursive($price, $inventory);
    }
}
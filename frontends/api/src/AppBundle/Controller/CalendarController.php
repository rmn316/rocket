<?php

namespace AppBundle\Controller;

use AppBundle\Service\CalendarRoomBuilder;
use AppBundle\Service\InventoryBuilder;
use AppBundle\Service\InventoryUpdater;
use AppBundle\Service\PriceUpdater;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CalendarController extends Controller
{
    public function getAction(Request $request)
    {
        /** @var CalendarRoomBuilder $builder */
        $builder = $this->get('app.service.calendar_builder');

        try {
            $result = $builder->build(
                new DateTime($request->query->get('start')),
                new DateTime($request->query->get('end'))
            );
        } catch (Exception $e) {
            throw $e;
        }
        return new JsonResponse($result);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function postCalendarPriceAction(Request $request)
    {
        /** @var PriceUpdater $service */
        $service = $this->get('app.service.price_updater');

        $parameters = json_decode($request->getContent(), true);

        try {
            $result = $service->update(
                new DateTime($parameters['date']['start']),
                new DateTime($parameters['date']['end']),
                $parameters['price'],
                [
                    'days' => isset($parameters['days']) ? array_keys($parameters['days']) : [],
                    'room' => $parameters['room']
                ]
            );
        } catch (Exception $e) {
            throw $e;
        }
        return new JsonResponse($result);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function postCalendarInventoryAction(Request $request)
    {
        /** @var InventoryUpdater $service */
        $service = $this->get('app.service.inventory_updater');

        $parameters = json_decode($request->getContent(), true);

        try {
            $result = $service->update(
                new DateTime($parameters['date']['start']),
                new DateTime($parameters['date']['end']),
                $parameters['inventory'],
                [
                    'days' => isset($parameters['days']) ? array_keys($parameters['days']) : [],
                    'room' => $parameters['room']
                ]
            );
        } catch (Exception $e) {
            throw $e;
        }
        return new JsonResponse($result);
    }
}

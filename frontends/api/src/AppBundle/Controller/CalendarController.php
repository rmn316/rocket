<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CalendarRoom;
use AppBundle\Form\CalendarRoomInventoryType;
use AppBundle\Form\CalendarRoomPriceType;
use AppBundle\Form\CalendarRoomType;
use AppBundle\Service\CalendarRoomBuilder;
use AppBundle\Service\CalendarUpdater;
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
        $builder = $this->get('app.service.calendar.builder');

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

        $request->request->replace(json_decode($request->getContent(), true));

        $form = $this->createForm(CalendarRoomPriceType::class, new CalendarRoom());
        $form->submit($request->request->all());
        try {
            $result = $service->update($form);
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

        $request->request->replace(json_decode($request->getContent(), true));

        $form = $this->createForm(CalendarRoomInventoryType::class, new CalendarRoom());
        $form->submit($request->request->all());

        try {
            $result = $service->update($form);
        } catch (Exception $e) {
            throw $e;
        }
        return new JsonResponse($result);
    }

    public function postCalendarAction(Request $request)
    {
        /** @var CalendarUpdater $service */
        $service = $this->get('app.service.calendar_updater');

        $request->request->replace(json_decode($request->getContent(), true));

        $form = $this->createForm(CalendarRoomType::class, new CalendarRoom());
        $form->submit($request->request->all());

        try {
            $result = $service->update($form);
        } catch (Exception $e) {
            throw $e;
        }
        return new JsonResponse($result);
    }
}

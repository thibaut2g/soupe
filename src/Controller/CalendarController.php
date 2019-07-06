<?php

namespace App\Controller;

use App\Service\CalendarService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{

    /**
     * @Route("/saveDate/{date}", name="saveDate")
     * @param CalendarService $calendarService
     * @param $date
     * @return Response
     */
    public function saveDate(CalendarService $calendarService, $date)
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        try {
            if ($calendarService->saveDate($userId, $date)) {
                $response = "ok";
            } else {
                $response = "ko";
            }
        } catch (OptimisticLockException $e) {
            echo $e->getMessage();
        } catch (ORMException $e) {
            echo $e->getMessage();
        }
        return new Response($response);
    }

    /**
     * @Route("/unsubscribeDate/{date}", name="unsubscribeDate")
     * @param CalendarService $calendarService
     * @param $date
     * @return Response
     */
    public function unsubscribeDate(CalendarService $calendarService, $date)
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        try {
            if ($calendarService->unsubscribeDate($userId, $date)) {
                $response = "ok";
            } else {
                $response = "ko";
            }
        } catch (OptimisticLockException $e) {
            echo $e->getMessage();
        } catch (ORMException $e) {
            echo $e->getMessage();
        }
        return new Response($response);
    }
}

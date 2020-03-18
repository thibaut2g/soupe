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
     * @Route("/saveDate/{type}/{date}", name="saveDate")
     * @param CalendarService $calendarService
     * @param $type
     * @param $date
     * @return Response
     */
    public function saveDate(CalendarService $calendarService, $type, $date)
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();

        // interdiction de s'inscrire avec le compte administrateur
        if ($userId == 42) {
            return new Response("compte admin");
        }

        try {
            if ($calendarService->saveDate($userId, $date, $type)) {
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
     * @Route("/unsubscribeDate/{type}/{date}", name="unsubscribeDate")
     * @param CalendarService $calendarService
     * @param $type
     * @param $date
     * @return Response
     */
    public function unsubscribeDate(CalendarService $calendarService, $type, $date)
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        try {
            if ($calendarService->unsubscribeDate($userId, $date, $type)) {
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

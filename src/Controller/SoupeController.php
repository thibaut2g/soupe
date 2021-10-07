<?php
/**
 * Created by PhpStorm.
 * User: T2G-WEB
 * Date: 08/03/2020
 * Time: 16:21
 */

namespace App\Controller;

use App\Service\CalendarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SoupeController extends AbstractController
{

    static $months = [
        'January' => 'Janvier',
        'February' => 'Février',
        'March' => 'Mars',
        'April' => 'Avril',
        'May' => 'Mai',
        'June' => 'Juin',
        'July' => 'Juillet',
        'August' => 'Août',
        'September' => 'Septembre',
        'October' => 'Octobre',
        'November' => 'Novembre',
        'December' => 'Décembre'
    ];

    /**
     * @Route("/soupe", name="soupe")
     * @param CalendarService $calendarService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function soupe(CalendarService $calendarService)
    {
        $userId = $this->getUser()->getId();

        $isAdmin = $this->isGranted("ROLE_ADMIN");

        $tbody = $calendarService->getTbody($userId, false, "soupe");
        $weekDays = $calendarService->getWeekDays();
        $nextMonday = $calendarService->getNextMonday();
        $lastMonday = $calendarService->getLastMonday();

        return $this->render('soupe/index.html.twig', [
            'weekDays' => $weekDays,
            'weekNumber' => date('W'),
            'month' => self::$months[date('F')],
            'tbody' => $tbody,
            'nextMonday' => $nextMonday,
            'lastMonday' => $lastMonday,
            'isAdmin' => $isAdmin
        ]);
    }

    /**
     * @Route("/soupe/{monday}", name="monday_soupe")
     * @param CalendarService $calendarService
     * @param $monday
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function monday(CalendarService $calendarService, $monday)
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();

        $tbody = $calendarService->getTbody($userId, $monday, "soupe");
        $weekDays = $calendarService->getWeekDays($monday);
        $nextMonday = $calendarService->getNextMonday($monday);
        $lastMonday = $calendarService->getLastMonday($monday);

        $isAdmin = ($userId == 42);

        return $this->render('soupe/index.html.twig', [
            'weekDays' => $weekDays,
            'weekNumber' => date('W', strtotime($monday)),
            'month' => self::$months[date('F', strtotime($monday))],
            'tbody' => $tbody,
            'nextMonday' => $nextMonday,
            'lastMonday' => $lastMonday,
            'isAdmin' => $isAdmin
        ]);

    }
}
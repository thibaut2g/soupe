<?php

namespace App\Controller;

use App\Service\CalendarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
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
     * @Route("/")
     */
    public function index()
    {
        return $this->redirectToRoute("home");
    }

    /**
     * @Route("/home", name="home")
     * @param CalendarService $calendarService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function home(CalendarService $calendarService)
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();

        $tbody = $calendarService->getTbody($userId);
        $weekDays = $calendarService->getWeekDays();
        $nextMonday = $calendarService->getNextMonday();
        $lastMonday = $calendarService->getLastMonday();

        return $this->render('home/index.html.twig', [
            'weekDays' => $weekDays,
            'weekNumber' => date('W'),
            'month' => self::$months[date('F')],
            'tbody' => $tbody,
            'nextMonday' => $nextMonday,
            'lastMonday' => $lastMonday
        ]);
    }

    /**
     * @Route("/home/{monday}", name="monday")
     * @param CalendarService $calendarService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function monday(CalendarService $calendarService, $monday)
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();

        $tbody = $calendarService->getTbody($userId, $monday);
        $weekDays = $calendarService->getWeekDays($monday);
        $nextMonday = $calendarService->getNextMonday($monday);
        $lastMonday = $calendarService->getLastMonday($monday);

        return $this->render('home/index.html.twig', [
            'weekDays' => $weekDays,
            'weekNumber' => date('W', strtotime($monday)),
            'month' => self::$months[date('F', strtotime($monday))],
            'tbody' => $tbody,
            'nextMonday' => $nextMonday,
            'lastMonday' => $lastMonday
        ]);
    }
}

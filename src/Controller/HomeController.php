<?php

namespace App\Controller;

use App\Service\CalendarService;
use App\Service\ResponsableService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_USER")
 */
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
     * @param ResponsableService $responsableService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function home(CalendarService $calendarService, ResponsableService $responsableService)
    {
        $userId = $this->getUser()->getId();

        $isAdmin = $this->isGranted("ROLE_ADMIN");
      
        $tbody = $calendarService->getTbody($userId);
        $weekDays = $calendarService->getWeekDays();
        $nextMonday = $calendarService->getNextMonday();
        $lastMonday = $calendarService->getLastMonday();
        $responsables = $responsableService->getResponsables();

        return $this->render('home/index.html.twig', [
            'weekDays' => $weekDays,
            'weekNumber' => date('W'),
            'month' => self::$months[date('F')],
            'tbody' => $tbody,
            'nextMonday' => $nextMonday,
            'lastMonday' => $lastMonday,
            'responsables' => $responsables,
            'isAdmin' => $isAdmin
        ]);
    }

    /**
     * @Route("/home/{monday}", name="monday")
     * @param CalendarService $calendarService
     * @param ResponsableService $responsableService
     * @param $monday
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function monday(CalendarService $calendarService, ResponsableService $responsableService, $monday)
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();

        $tbody = $calendarService->getTbody($userId, $monday);
        $weekDays = $calendarService->getWeekDays($monday);
        $nextMonday = $calendarService->getNextMonday($monday);
        $lastMonday = $calendarService->getLastMonday($monday);
        $responsables = $responsableService->getResponsables();

        $isAdmin = $this->isGranted("ROLE_ADMIN");

        return $this->render('home/index.html.twig', [
            'weekDays' => $weekDays,
            'weekNumber' => date('W', strtotime($monday)),
            'month' => self::$months[date('F', strtotime($monday))],
            'tbody' => $tbody,
            'nextMonday' => $nextMonday,
            'lastMonday' => $lastMonday,
            'responsables' => $responsables,
            'isAdmin' => $isAdmin
        ]);
    }

    /**
     * @Route("/update-responsable/{day}", name="dayForm", methods={"post"})
     * @param Request $request
     * @param ResponsableService $responsableService
     * @param $day
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function dayForm(Request $request, ResponsableService $responsableService, $day)
    {
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $phone = $request->request->get('phone');

        $responsableService->saveResponsable($day, $name, $email, $phone);
        return $this->redirectToRoute('home');
    }
}

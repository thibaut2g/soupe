<?php

namespace App\Controller;

use App\Entity\Config;
use App\Service\CalendarService;
use App\Service\ResponsableService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    /** @var Config $config */
    private $config;


    public function __construct(EntityManagerInterface $em)
    {
        $configs = $em->getRepository(Config::class)->findAll();
        if (empty($configs)) {
            $this->config = new Config();
        } else {
            $this->config = array_pop($configs);
        }
    }

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
            'isAdmin' => $isAdmin,
            'maxParticipantNumber' => $calendarService->getMaxParticipantNumber(),
            'infoMessage' => $this->config->getMessageContent(),
            'messageColor' => $this->config->getMessageColor(),
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
            'isAdmin' => $isAdmin,
            'maxParticipantNumber' => $calendarService->getMaxParticipantNumber(),
            'infoMessage' => $this->config->getMessageContent(),
            'messageColor' => $this->config->getMessageColor(),
        ]);
    }

}

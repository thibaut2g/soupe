<?php

namespace App\Controller;

use App\Entity\Config;
use App\Service\CalendarService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_USER")
 */
class CalendarController extends AbstractController
{

    private $isOpenSubscription;

    public function __construct(EntityManagerInterface $em)
    {
        $configs =$em->getRepository(Config::class)->findAll();
        if (empty($configs)) {
            $config = new Config();
        } else {
            $config = array_pop($configs);
        }

        $this->isOpenSubscription = $config->getIsSubscriptionOpen();
    }

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
        if ($this->isGranted("ROLE_ADMIN")) {
            return new Response("compte admin");
        }

        if (!$this->isOpenSubscription) {
            return new Response("inscription fermée");
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

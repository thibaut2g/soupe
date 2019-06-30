<?php

namespace App\Service;

use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;

class CalendarService
{
    private $em;

    static $months = [
        'Janvier' => 1,
        'Février' => 2,
        'Mars' => 3,
        'Avril' => 4,
        'Mai' => 5,
        'Juin' => 6,
        'Juillet' => 7,
        'Août' => 8,
        'Septembre' => 9,
        'Octobre' => 10,
        'Novembre' => 11,
        'Decembre' => 12
    ];

    static $week = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday'
    ];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param $userId
     * @param $date
     * @return bool
     */
    public function saveDate($userId, $date)
    {
        $date = $this->getFormattedDate($date);
        $date = new \DateTime($date);

        $subscriptions = $this->em->getRepository(Subscription::class)
            ->findBy(['date' => $date]);
        if (count($subscriptions) >= 5)
            return false;

        foreach($subscriptions as $subscription) {
            if ($subscription->getUserId() == $userId)
                return false;
        }


        $subscription = new Subscription();
        $subscription->setDate($date);
        $subscription->setUserId($userId);
        $this->em->persist($subscription);
        $this->em->flush();
        return true;
    }

    private function getFormattedDate($date)
    {
        $date = explode(" ", $date);

        return $date[3]."-".self::$months[$date[2]]."-".$date[1];
    }

    public function getTbody($userId, $date = false)
    {
        if (false === $date)
            $date = new \DateTime('monday this week');
        else
            $date = new \DateTime($date);

        foreach (self::$week as $day) {
            $calendar[$day] =  $this->em->getRepository(Subscription::class)
                                        ->findBy(['date' => $date]);
            $date->modify('+1 day');
        }

        $tbody = '';

        // 5 participants maximum
        for ($i = 1; $i <= 5; $i++) {
            $tr = '';
            $count = 0;
            foreach (self::$week as $day) {
                $tr .= "<td>";
                if ($date = array_pop($calendar[$day])) {
                    $tr .= ($date->getUserId() == $userId) ? "<i class=\"material-icons green-text\">verified_user</i>" : "<i class=\"material-icons grey-text\">verified_user</i>";
                    $count++;
                }
                $tr .= "</td>";
            }

            if ($count == 0) {
                break;
            }

            $tbody .= "<tr>\n";
            $tbody .= $tr;
            $tbody .= "</tr>\n";
        }
        return $tbody;
    }

    public function getWeekDays($date = false)
    {
        if (false === $date)
            $date = new \DateTime('monday this week');
        else
            $date = new \DateTime($date);


        for ($i = 0; $i <= 6; $i++) {
            $weekDays[self::$week[$i]] = $date->format('d');
            $date->modify('+1 day');
        }
        return $weekDays;
    }

    public function getNextMonday($date = false)
    {
        if (false === $date)
            $date = new \DateTime('monday this week');
        else
            $date = new \DateTime($date);

        $date->modify('+1 week');

        return $date->format('Y-m-d');
    }

    public function getLastMonday($date = false)
    {
        if (false === $date)
            $date = new \DateTime('monday this week');
        else
            $date = new \DateTime($date);

        $date->modify('-1 week');

        return $date->format('Y-m-d');
    }
}
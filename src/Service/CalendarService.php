<?php

namespace App\Service;

use App\Entity\ActiveDate;
use App\Entity\Config;
use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

class CalendarService
{
    const ROW_MIN_PARTICIPANT = 1;
    const MAX_PARTICIPANT = 5;
    private $em;

    private const MONTHS = [
        'Janvier' => "01",
        'Février' => "02",
        'Mars' => "03",
        'Avril' => "04",
        'Mai' => "05",
        'Juin' => "06",
        'Juillet' => "07",
        'Août' => "08",
        'Septembre' => "09",
        'Octobre' => "10",
        'Novembre' => "11",
        'Decembre' => "12"
    ];

    private const WEEK_DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday'
    ];

    private $twig;
    /** @var Config $config */
    private $config;

    public function __construct(EntityManagerInterface $entityManager, Environment $twig)
    {
        $this->em = $entityManager;
        $this->twig = $twig;
        $configs =$this->em->getRepository(Config::class)->findAll();
        if (empty($configs)) {
            $this->config = new Config();
        } else {
            $this->config = array_pop($configs);
        }
    }

    /**
     * @param $userId
     * @param $date
     * @param $type
     * @return bool
     */
    public function saveDate($userId, $date, $type)
    {
        if (!$this->validateDate($date)) {
            $date = $this->getFormattedDate($date);
            if (!$date) {
                return false;
            }
        }
        $date = new \DateTime($date);

        $subscriptions = $this->em->getRepository(Subscription::class)
            ->findBy(['date' => $date, "type" => $type, "isRemoved" => NULL]);
        if (count($subscriptions) >= $this->getMaxParticipantNumber() || $this->isASunday($date) || $this->isGreaterThanThreeMonths($date))
            return false;

/**        foreach($subscriptions as $subscription) {
            if ($subscription->getUserId() == $userId)
                return false;
        }
*/

        $subscription = new Subscription();
        $subscription->setDate($date);
        $subscription->setType($type);
        $subscription->setUserId($userId);
        $this->em->persist($subscription);
        $this->em->flush();
        return true;
    }

    private function getFormattedDate($date)
    {
        $date = explode(" ", $date);

        $date = $date[3]."-".self::MONTHS[$date[2]]."-".$date[1];

        if (!$this->validateDate($date))
            return false;

        return $date;
    }

    public function getTbody($userId, $monday = false, $type = "main")
    {
        if (false === $monday)
            $monday = new \DateTime('monday this week');
        else
            $monday = new \DateTime($monday);

        $calendar = $this->getCalendarSubscriptionByDayBeginningBy($monday, $type);

        $tbody = '';

        for ($rowNumber = self::ROW_MIN_PARTICIPANT; $rowNumber <= ($this->getMaxParticipantNumber() + 1); $rowNumber++) {

            $tr = '';
            $lineCount = 0;

            foreach (self::WEEK_DAYS as $day) {
                $tr .= "<td>";

                    if(!empty($calendar[$day][$rowNumber])) {
                        /* @var \App\Entity\Subscription $date */
                        $date = $calendar[$day][$rowNumber];

                        $tr .= $this->getUserSubscription($date->getUserId(), $userId);
                        $lineCount++;
                    }

                $tr .= "</td>";
            }

            if ($lineCount == 0 OR $rowNumber == ($this->getMaxParticipantNumber() + 1)) {
                $tbody .= $this->getSubscribeButtons($calendar, $userId, $type);
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
            $weekDays[self::WEEK_DAYS[$i]] = $date->format('d');
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

    private function getCalendarSubscriptionByDayBeginningBy($date, $type)
    {
        foreach (self::WEEK_DAYS as $day) {
            $calendar[$day] =  $this->em->getRepository(Subscription::class)
                ->findBy(['date' => $date, "type" => $type, 'isRemoved' => NULL]);
            array_unshift($calendar[$day], $date->format('Y-m-d'));
            $date->modify('+1 day');
        }
        return $calendar;
    }


    private function getSubscribeButton($date, $userId, $type)
    {
        $askedDate = new \DateTime($date);
        $subscriptions = $this->em->getRepository(Subscription::class)
            ->findBy(['date' => $askedDate, "userId" => $userId, "type" => $type, "isRemoved" => NULL]);

        return $this->twig->render('helper/subscribeButton.html.twig', [
            'subscriptions' => $subscriptions,
            'date' => $date,
            'isMaxNumber' => $this->getMaxParticipantNumber() <= count($subscriptions),
        ]);

    }


    private function getSubscribeButtons($calendar, $userId, $type)
    {
        $subscribeButtons = "<tr>";

        foreach (self::WEEK_DAYS as $day) {
            $subscribeButtons .= "<td>";
            $date = $calendar[$day][0];
            if ($this->isActive($date) && $this->config->getIsSubscriptionOpen() !== false) {
                $subscribeButtons .= $this->getSubscribeButton($date, $userId, $type);
            } else {
                $subscribeButtons .= $this->getCloseButton();
            }

            $subscribeButtons .= "</td>";
        }
        $subscribeButtons .= "<tr>";
        return $subscribeButtons;

    }

    private function validateDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    public function unsubscribeDate($userId, $date, $type)
    {
        $date = new \DateTime($date);
        $subscriptions = $this->em->getRepository(Subscription::class)
            ->findBy(['date' => $date, "userId" => $userId, "type" => $type, "isRemoved" => NULL]);
        foreach ($subscriptions as $subscription) {
            $subscription->setIsRemoved(true);
            $this->em->persist($subscription);
        }
        $this->em->flush();

        return true;
    }

    private function getUserSubscription($userId, $SelfUserId)
    {
        $user = $this->em->getRepository(User::class)
            ->findOneBy(['id' => $userId]);

        $selfUser = $this->em->getRepository(User::class)
            ->findOneBy(['id' => $SelfUserId]);

        return $this->twig->render('helper/subscribeButtons.html.twig', [
            'isCurrentUser' => ($SelfUserId == $userId),
            'isAdmin' => in_array('ROLE_ADMIN', $selfUser->getRoles()),
            "user" => $user
        ]);
    }

    private function getCloseButton()
    {
        return "<i class='material-icons'>clear</i>";
    }

    private function isActive($date)
    {
        $date = new \DateTime($date);

        $day = $date->format('l');

        if ('Sunday' == $day) {
            return false;
        }

        $isUnActive = $this->em->getRepository(ActiveDate::class)
            ->findBy(['date' => $date]);

        return !$isUnActive;
    }

    private function isASunday($date)
    {
        return $date->format('D') == 'Sun';
    }

    public function getMaxParticipantNumber()
    {
        if (!empty($this->config->getMaxParticipantNumber())) {
            return $this->config->getMaxParticipantNumber();
        }

        return self::MAX_PARTICIPANT;
    }

    private function isGreaterThanThreeMonths($date)
    {
        $ThreeMonthLater = new \DateTime();
        $ThreeMonthLater->modify('+3 month');
        return $date > $ThreeMonthLater;
    }
}
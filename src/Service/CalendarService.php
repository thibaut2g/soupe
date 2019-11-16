<?php

namespace App\Service;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CalendarService
{
    private $em;

    static $months = [
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
        if (!$this->validateDate($date))
            $date = $this->getFormattedDate($date);
        $date = new \DateTime($date);

        $subscriptions = $this->em->getRepository(Subscription::class)
            ->findBy(['date' => $date, "isRemoved" => NULL]);
        if (count($subscriptions) >= 5)
            return false;

/**        foreach($subscriptions as $subscription) {
            if ($subscription->getUserId() == $userId)
                return false;
        }
*/

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

        $date = $date[3]."-".self::$months[$date[2]]."-".$date[1];

        if (!$this->validateDate($date))
            return "2019-01-01";

        return $date;
    }

    public function getTbody($userId, $monday = false)
    {
        if (false === $monday)
            $monday = new \DateTime('monday this week');
        else
            $monday = new \DateTime($monday);

        $calendar = $this->getCalendar($monday);

        $tbody = '';

        // 5 participants maximum
        for ($i = 1; $i <= 6; $i++) {
            $tr = '';
            $count = 0;

            foreach (self::$week as $day) {
                $tr .= "<td>";
                $date = array_pop($calendar[$day]);
                if ($date && is_object($date)) {
                    $tr .= $this->getUserSubscription($date->getUserId(), $userId);
                    $count++;
                } elseif($i == 6) {
                    $tr .= $this->getSubscribeButton($date, $userId);
                } else {
                    array_push($calendar[$day], $date);
                }

                $tr .= "</td>";
            }

            if ($count == 0 && $i != 6) {
                $tbody .= $this->getSubscribeButtons($calendar, $userId);
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

    private function getCalendar($date)
    {
        foreach (self::$week as $day) {
            $calendar[$day] =  $this->em->getRepository(Subscription::class)
                ->findBy(['date' => $date, 'isRemoved' => NULL]);
            array_unshift($calendar[$day], $date->format('Y-m-d'));
            $date->modify('+1 day');
        }
        return $calendar;
    }

    private function getSubscribeButton($date, $userId)
    {
        $askedDate = new \DateTime($date);
        $subscriptions = $this->em->getRepository(Subscription::class)
            ->findBy(['date' => $askedDate, "userId" => $userId, "isRemoved" => NULL]);

        if (!empty($subscriptions))
            return "<i class='material-icons subscribe blue-text' data-date='".$date."'>control_point</i>
                    <i class='material-icons unsubscribe red-text' data-date='".$date."'>highlight_off</i>";

        return "<i class='material-icons subscribe blue-text' data-date='".$date."'>control_point</i>";
    }

    private function getSubscribeButtons($calendar, $userId)
    {
        $subscribeButtons = "<tr>";

        foreach (self::$week as $day) {
            $subscribeButtons .= "<td>";
            $date = array_pop($calendar[$day]);
            $subscribeButtons .= $this->getSubscribeButton($date, $userId);

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

    public function unsubscribeDate($userId, $date)
    {
        $date = new \DateTime($date);
        $subscriptions = $this->em->getRepository(Subscription::class)
            ->findBy(['date' => $date, "userId" => $userId, "isRemoved" => NULL]);
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

        $result = ($SelfUserId == $userId) ? "<i class=\"material-icons green-text userinfo\">verified_user</i>" : "<i class=\"material-icons grey-text userinfo\">verified_user</i>";

        if (in_array('ROLE_ADMIN', $selfUser->getRoles()))
            $result .= "<div class=\"card blue-grey darken-1 hiddendiv\" style='position: absolute'>
                            <div class=\"card-content white-text\">
                              <span class=\"card-title\">".$user->getNomComplet()."</span>
                              <p>
                                ".$user->getEmail()."
                                <br>
                                ".$user->getPhone()."
                              </p>
                            </div>
                          </div>";


        return $result;
    }
}
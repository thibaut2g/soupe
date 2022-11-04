<?php
/**
 * Created by PhpStorm.
 * User: T2G-WEB
 * Date: 18/10/2022
 * Time: 20:37
 */

namespace App\Service;


use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class RelanceMailService
{
    private $mailer;
    private $em;

    public function __construct(MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->em = $entityManager;
    }

    public function relanceMail() {

        $date = new \DateTime();
        $date->modify('+1 day');
        $subscriptions = $this->em->getRepository(Subscription::class)->findBy(["date" => $date, "isRemoved" => NULL]);

        var_dump($subscriptions);die;

        /** @var Subscription $subscription */
        /** @var User $user */
        foreach ($subscriptions as $subscription) {
            $user  = $this->em->getRepository(User::class)->findOneBy(["id" => $subscription->getUserId()]);

            $email = (new TemplatedEmail())
                ->from(Address::create('Ne pas répondre - Soupe <noreply.soupesacrecoeur@gmail.com>'))
                ->to('thibaut.de-gouberville@2018.icam.fr')
                ->subject('Rappel : Soupe du Sacré Coeur demain' . $user->getEmail())
                ->htmlTemplate('emails/relance.html.twig');

            $this->mailer->send($email);
        }

    }
}
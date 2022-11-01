<?php
/**
 * Created by PhpStorm.
 * User: T2G-WEB
 * Date: 18/10/2022
 * Time: 20:37
 */

namespace App\Service;


use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class RelanceMailService
{
    public function relanceMail(MailerInterface $mailer) {

        $email = (new Email())
            ->from('noreply.soupesacrecoeur@gmail.com')
            ->to('thibaut.de-gouberville@2018.icam.fr')
            ->subject('Test Soupe !')
            ->htmlTemplate('emails/relance.html.twig');

        $mailer->send($email);

    }
}
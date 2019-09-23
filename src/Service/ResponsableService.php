<?php
/**
 * Created by PhpStorm.
 * User: T2G-WEB
 * Date: 23/09/2019
 * Time: 15:09
 */

namespace App\Service;


use App\Entity\Responsable;
use Doctrine\ORM\EntityManagerInterface;

class ResponsableService
{

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getResponsables() {
        $responsables = $this->em->getRepository(Responsable::class);
        $responsables = $responsables->findAll();

        $result = [];

        foreach ($responsables as $responsable) {
            $result[$responsable->getDay()] = $responsable;
        }
        return $result;
    }

    /**
     * @param $day
     * @param $name
     * @param $email
     * @param $phone
     * @return bool
     */
    public function saveResponsable($day, $name, $email, $phone)
    {
        $responsables = $this->em->getRepository(Responsable::class);

        $responsable = $responsables->findOneBy(['day' => $day]);

        if (!$responsable) {
            $responsable = new Responsable();
            $responsable->setDay($day);
        }

        $responsable->setName($name);
        $responsable->setEmail($email);
        $responsable->setPhone($phone);

        $this->em->persist($responsable);
        $this->em->flush();

        return true;
    }

}
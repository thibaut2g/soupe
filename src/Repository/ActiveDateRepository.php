<?php

namespace App\Repository;

use App\Entity\ActiveDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ActiveDate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActiveDate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActiveDate[]    findAll()
 * @method ActiveDate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiveDateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ActiveDate::class);
    }

    // /**
    //  * @return ActiveDate[] Returns an array of ActiveDate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ActiveDate
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

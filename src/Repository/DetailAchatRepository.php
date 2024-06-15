<?php

namespace App\Repository;

use App\Entity\DetailAchat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DetailAchat>
 *
 * @method DetailAchat|null find($id, $lockMode = null, $lockVersion = null)
 * @method DetailAchat|null findOneBy(array $criteria, array $orderBy = null)
 * @method DetailAchat[]    findAll()
 * @method DetailAchat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetailAchatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetailAchat::class);
    }

//    /**
//     * @return DetailAchat[] Returns an array of DetailAchat objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DetailAchat
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

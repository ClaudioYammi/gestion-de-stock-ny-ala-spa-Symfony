<?php

namespace App\Repository;

use App\Entity\DetailVente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DetailVente>
 *
 * @method DetailVente|null find($id, $lockMode = null, $lockVersion = null)
 * @method DetailVente|null findOneBy(array $criteria, array $orderBy = null)
 * @method DetailVente[]    findAll()
 * @method DetailVente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetailVenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetailVente::class);
    }

//    /**
//     * @return DetailVente[] Returns an array of DetailVente objects
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

//    public function findOneBySomeField($value): ?DetailVente
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

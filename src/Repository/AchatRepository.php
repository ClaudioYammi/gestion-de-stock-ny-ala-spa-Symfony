<?php

namespace App\Repository;

use App\Entity\Achat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Achat>
 *
 * @method Achat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Achat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Achat[]    findAll()
 * @method Achat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Achat::class);
    }

    /**
     * Finds Produits based on search criteria on allowed attributes.
     *
     * @param array $criteria An associative array where key is the allowed entity property name and value is the search term
     * @param array $orderBy  An array of orderBy clauses (e.g., ['designation' => 'ASC'])
     * @param int|null $limit  Limits the number of results
     * @param int|null $offset  Starts from a specific offset in the results
     *
     * @return Achat[] An array of Produit entities that match the search criteria on allowed attributes
     */
    public function findByCriteriaAllowed(array $criteria = [], array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $allowedAttributes = [ 'numfacture', 'dateachat', 'datfacture']; // Define allowed attributes

        $qb = $this->createQueryBuilder('p');

        // Build WHERE clause dynamically based on allowed criteria
        $this->addCriteriaToQueryBuilderAllowed($qb, $criteria, $allowedAttributes);

        // Add order by clauses if provided
        if ($orderBy) {
            foreach ($orderBy as $field => $direction) {
                $qb->orderBy('p.' . $field, $direction);
            }
        }

        // Set limit and offset if provided
        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }
        if (isset($offset)) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    private function addCriteriaToQueryBuilderAllowed(QueryBuilder $qb, array $criteria, array $allowedAttributes): void
    {
        foreach ($criteria as $field => $value) {
            if (in_array($field, $allowedAttributes)) {
                // Use LIKE operator for other allowed attributes
                $qb->andWhere('p.' . $field . ' LIKE :'.$field)
                ->setParameter($field, '%' . $value . '%');
            }
        }
    }

    
//    /**
//     * @return Achat[] Returns an array of Achat objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Achat
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

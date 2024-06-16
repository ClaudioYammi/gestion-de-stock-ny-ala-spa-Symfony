<?php

namespace App\Repository;

use App\Entity\Inventaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Inventaire>
 */
class InventaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventaire::class);
    }

    /**
     * Finds Produits based on search criteria on allowed attributes.
     *
     * @param array $criteria An associative array where key is the allowed entity property name and value is the search term
     * @param array $orderBy  An array of orderBy clauses (e.g., ['designation' => 'ASC'])
     * @param int|null $limit  Limits the number of results
     * @param int|null $offset  Starts from a specific offset in the results
     *
     * @return Inventaire[] An array of Produit entities that match the search criteria on allowed attributes
     */
    public function findByCriteriaAllowed(array $criteria = [], array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $allowedAttributes = ['update_at', 'reference', 'stockinventaire', 'stockutiliser' ]; // Define allowed attributes

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
                if ($field === 'reference') {
                    // Join the reference entity (replace with your actual entity name)
                    $qb->innerJoin('p.reference', 'e');
                    $qb->andWhere('e.designation LIKE :reference') // Replace 'name' with the actual field name in reference
                        ->setParameter('reference', '%' . $value . '%');
                } else {
                    // Use LIKE operator for other allowed attributes
                $qb->andWhere('p.' . $field . ' LIKE :'.$field)
                ->setParameter($field, '%' . $value . '%');
                }
            }
        }
    }

    

    //    /**
    //     * @return Inventaire[] Returns an array of Inventaire objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Inventaire
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

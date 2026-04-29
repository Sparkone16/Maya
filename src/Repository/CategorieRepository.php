<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Categorie>
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }
        /**
     * @return Query
     */
    public function findAllWithStats(): array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT c.libelle as libelle,
                    COUNT(p.id) as nbProduits,
                    COALESCE(MIN(p.prix), 0) as prixMin,
                    COALESCE(MAX(p.prix), 0) as prixMax,
                    COALESCE(AVG(p.prix), 0) as prixMoyen
           FROM App\Entity\Categorie c
           join c.produits p    
           GROUP BY c.id
           ORDER BY c.libelle ASC'
        );
        return $query->getResult();

        // --- version avec querybuilder
        // return $this->createQueryBuilder('c')
        //     ->select(
        //         'c.libelle as libelle',
        //         'COUNT(p.id) as nbProduits',
        //         'COALESCE(MIN(p.prix), 0) as prixMin',
        //         'COALESCE(MAX(p.prix), 0) as prixMax',
        //         'COALESCE(AVG(p.prix), 0) as prixMoyen'
        //     )
        //     ->leftJoin('c.produits', 'p') // 'produits' est le nom de la relation dans l'entité Categorie
        //     ->groupBy('c.id')
        //     ->getQuery()
        //     ->getResult();
    }



    //    /**
    //     * @return Categorie[] Returns an array of Categorie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Categorie
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findAllOrderByLibelle(): array {
        return $this->createQueryBuilder('c')
            ->orderBy('c.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

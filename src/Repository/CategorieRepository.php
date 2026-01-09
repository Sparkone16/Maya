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
    public function findAllWithStats(): array
    {
        return $this->createQueryBuilder('c')
            ->select(
                'c.libelle as name',
                'COUNT(p.id) as nbProduits',
                'COALESCE(MIN(p.prix), 0) as prixMin',
                'COALESCE(MAX(p.prix), 0) as prixMax',
                'COALESCE(AVG(p.prix), 0) as prixMoyen'
            )
            ->leftJoin('c.produits', 'p') // 'produits' est le nom de la relation dans ton entité Categorie
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
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
}

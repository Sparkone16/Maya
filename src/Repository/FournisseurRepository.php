<?php

namespace App\Repository;

use App\Entity\Fournisseur;
use App\Entity\FournisseurRecherche;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;


/**
 * @extends ServiceEntityRepository<Fournisseur>
 */
class FournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fournisseur::class);
    }

    /**
     * @return Query
     */
    public function findAllByCriteria(FournisseurRecherche $fournisseurRecherche): Query
    {
        // le "p" est un alias utilisé dans la requête
        $qb = $this->createQueryBuilder('f')
            ->orderBy('f.nom', 'ASC');

        if ($fournisseurRecherche->getNom()) {
            $qb->andWhere('f.nom LIKE :nom')
                ->setParameter('nom', $fournisseurRecherche->getNom().'%');
        }

        if ($fournisseurRecherche->getPrenom()) {
            $qb->andWhere('f.prenom >= :prenom')
                ->setParameter('prenom', $fournisseurRecherche->getPrenom());
        }

        if ($fournisseurRecherche->getAdresse()) {
            $qb->andWhere('f.adresse < :adresse')
                ->setParameter('adresse', $fournisseurRecherche->getAdresse());
        }

        if ($fournisseurRecherche->getMail()) {
            $qb->andWhere('f.mail < :mail')
                ->setParameter('mail', $fournisseurRecherche->getMail());
        }

        return $qb->getQuery();
    }

    /**
    * @return Query
    */
   public function findAllOrderByLibelle(): Query
   {
       $entityManager = $this->getEntityManager();
       $query = $entityManager->createQuery(
           'SELECT f
           FROM App\Entity\Fournisseur f
           ORDER BY f.nom ASC'
       );

       // retourne un tableau d'objets de type Fournisseur
       return $query;
   }


    //    /**
    //     * @return Fournisseur[] Returns an array of Fournisseur objects
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

    //    public function findOneBySomeField($value): ?Fournisseur
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

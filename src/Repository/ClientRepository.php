<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\ClientRecherche;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;


/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @return Query
     */
    public function findAllByCriteria(ClientRecherche $clientRecherche): Query
    {
        // le "p" est un alias utilisé dans la requête
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.nom', 'ASC');

        if ($clientRecherche->getNom()) {
            $qb->andWhere('c.nom LIKE :nom')
                ->setParameter('nom', $clientRecherche->getNom().'%');
        }

        if ($clientRecherche->getPrenom()) {
            $qb->andWhere('c.prenom >= :prenom')
                ->setParameter('prenom', $clientRecherche->getPrenom());
        }

        if ($clientRecherche->getAdresse()) {
            $qb->andWhere('c.adresse < :adresse')
                ->setParameter('adresse', $clientRecherche->getAdresse());
        }

        if ($clientRecherche->getMail()) {
            $qb->andWhere('c.mail < :mail')
                ->setParameter('mail', $clientRecherche->getMail());
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
           'SELECT c
           FROM App\Entity\Client c
           ORDER BY c.nom ASC'
       );

       // retourne un tableau d'objets de type Client
       return $query;
   }


    //    /**
    //     * @return Client[] Returns an array of Client objects
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

    //    public function findOneBySomeField($value): ?Client
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

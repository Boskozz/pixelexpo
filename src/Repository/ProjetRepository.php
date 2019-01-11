<?php

namespace App\Repository;

use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Projet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Projet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Projet[]    findAll()
 * @method Projet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    /**
     * Utilisé pour l'index projet de la section creator avec pagination
     * @param $user
     * @return \Doctrine\ORM\Query Returns an array of Projet objects
     */
    public function findByUserQuery($user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :val')
            ->setParameter('val', $user)
            //->andWhere('p.prive = true')
            ->orderBy('p.title', 'ASC')
            //->setMaxResults(10)
            ->getQuery()
            //->getResult()
        ;
    }

    /**
     * Utilisé pour l'index projet de la section administrateur avec pagination
     */
    public function findAllQuery(){
        return $this->createQueryBuilder('p')
                    ->orderBy('p.id', 'DESC')
                    ->getQuery();
    }

     /**
      * @return Projet[] Returns an array of Projet objects
      */
    public function findByUserPublic($user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :val')
            ->setParameter('val', $user)
            ->andWhere('p.prive = false')
            ->orderBy('p.title', 'ASC')
            //->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
//
//    /**
//     * Récupération des infos MesProjets dans le Dashboard
//     * Projet - Titre / count albums / Album - Titre / count pictures
//     * @param $user
//     * @return mixed
//     */
//    public function findMesProjets($user) {
//        return $this->createQueryBuilder('p')
//                    ->select('p.title as projetTitle')
//                    ->join('p.albums', 'a')
//                    ->join('a.pictures', 'pi')
//                    //->groupBy('p.albums')
//                    ->where('p.author = :val')
//                    ->setParameter('val', $user)
//                    ->addOrderBy('p.title', 'ASC')
//                    ->addOrderBy('a.title', 'ASC')
//                    ->getQuery()
//                    ->getResult()
//        ;
//    }

    /*
    public function findOneBySomeField($value): ?Projet
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

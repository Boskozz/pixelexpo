<?php

namespace App\Repository;

use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Album|null find($id, $lockMode = null, $lockVersion = null)
 * @method Album|null findOneBy(array $criteria, array $orderBy = null)
 * @method Album[]    findAll()
 * @method Album[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Album::class);
    }

    /**
     * @param $user
     * @return \Doctrine\ORM\Query Returns an array of Album objects
     */
    public function findByProjetUserQuery($user)
    {
        return $this->createQueryBuilder('a')
            ->join('a.projet', 'p')
            ->andWhere('p.author = :val')
            ->setParameter('val', $user)
            ->orderBy('p.title', 'ASC')
           // ->setMaxResults(10)
            ->getQuery()
        ;
    }

    public function findAllQuery(){
        return $this->createQueryBuilder('a')
                    ->getQuery();
    }

    /**
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findBySlugPublic($slug)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.slug = :val')
            ->setParameter('val', $slug)
            ->andWhere('a.private = false')
            ->orderBy('a.title', 'ASC')
            //->setMaxResults(10)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * Récupération des infos MesProjets dans le Dashboard
     * Projet - Titre / count albums / Album - Titre / count pictures
     * @param $user
     * @return mixed
     */
    public function findMesProjets($user) {
        return $this->createQueryBuilder('a')
            ->select('a', 'pr')
            ->join('a.projet', 'pr')
            ->leftJoin('a.pictures', 'pi')
            //->groupBy('pr')
            ->where('pr.author = :val')
            ->setParameter('val', $user)
            ->addOrderBy('a.title', 'ASC')
            //->addOrderBy('a.title', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}

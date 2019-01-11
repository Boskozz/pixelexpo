<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Dashboard createur "Mes derniers commentaires"
     * @param $user
     * @param Integer $limit
     * @return Comment[] Returns an array of Comment objects
     */
    public function findByAuthorLimit($user, $limit)
    {
        $query = $this->createQueryBuilder('c')
            ->join('c.picture', 'p')
            ->join('p.album', 'a')
            ->join('a.projet', 'pr')
            ->join('pr.author', 'u')
            ->select('c.content', 'p.title', 'u.username', 'p.id as pid', 'p.slug as pslug', 'c.rating','p.filename')
            ->andWhere('c.author = :val')
            ->setParameter('val', $user)
            ->orderBy('c.id', 'DESC')
        ;
        if ($limit != 0) {
            $query = $query->setMaxResults($limit)
                ->getQuery()
                ->getResult();
        } else {
            $query = $query->getQuery();
        }
        return $query;
    }

    /**
     * Dashboard createur "Derniers commentaires sur mes photos"
     * @param $user
     * @param Integer $limit
     * @return mixed
     */
    public function findMesPhotosComment($user, $limit) {
        $query = $this->createQueryBuilder('c')
                    ->join('c.picture', 'p')
                    ->join('p.album', 'a')
                    ->join('a.projet', 'pr')
                    ->join('c.author', 'au')
                    ->select('c.content', 'p.title', 'au.username', 'p.id as pid', 'p.slug as pslug','p.filename', 'c.rating')
                    ->where('pr.author = :val')
                    ->setParameter('val', $user)
                    ->orderBy('c.id', 'DESC')
            ;
        if ($limit != 0) {
            return $query->setMaxResults($limit)
                           ->getQuery()
                           ->getResult();
        } else {
            return $query->getQuery();
        }
    }

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

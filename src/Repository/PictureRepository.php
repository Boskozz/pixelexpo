<?php

namespace App\Repository;

use App\Entity\ImageSearch;
use App\Entity\Picture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Picture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Picture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Picture[]    findAll()
 * @method Picture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PictureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Picture::class);
    }

     /**
      * Récupération de la liste des images
      * @return Picture[] Returns an array of Picture objects
      */
    public function findByPictureUser($user)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.description', 'p.createdAt', 'p.updatedAt', 'p.title', 'p.slug',
                'pr.title as projet', 'p.id', 'a.title as album', 'a.slug as alslug',
                'pr.slug as prslug', 'COUNT(c) as nbCom', 'p.private')
            ->join('p.album', 'a')
            ->leftJoin('p.comments', 'c')
            ->join('a.projet', 'pr')
            ->andWhere('pr.author = :val')
            ->setParameter('val', $user)
            ->groupBy('p')
            ->addOrderBy('pr.title', 'ASC')
            ->addOrderBy('a.title', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        return $qb;
    }
//
//    public function pictureUserQuery(){
//        $qb =  ->join('p.album', 'a')
//            ->join('a.projet', 'pr')
//            ->andWhere('pr.author = :val')
//            ->setParameter('val', $user)
//            ->addOrderBy('pr.title', 'ASC')
//            ->addOrderBy('a.title', 'ASC')
//    }

     /**
      * Récupération les images pour l'utilisateur en cours selon son orientation
      * @return Picture[] Returns an array of Picture objects
      */
    public function findGalerieUser($user, $orientation)
    {
        return $this->createQueryBuilder('p')
            ->join('p.album', 'a')
            ->join('a.projet', 'pr')
            ->andWhere('pr.author = :val')
            ->andWhere('p.orientation = :val2')
            ->setParameter('val', $user)
            ->setParameter('val2', $orientation)
            ->addOrderBy('pr.title', 'ASC')
            ->addOrderBy('a.title', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Récupération les images pour l'utilisateur en cours selon son orientation
     * @param ImageSearch $search
     * @return \Doctrine\ORM\Query Returns an array of Picture objects
     */
    public function findAllPublicQuery(ImageSearch $search)
    {
        $query = $this->createQueryBuilder('p')
            ->join('p.album', 'a')
            ->join('a.projet', 'pr')
            ->join('pr.author', 'u')
            ->select('p, u.username as username')
            ->andWhere('p.private = false')
            ->addOrderBy('p.title', 'ASC')
        ;

        if ($search->getOrientation()){
            $query = $query
                ->andWhere('p.orientation = :orientation')
                ->setParameter('orientation', $search->getOrientation());
        }

        if ($search->getEtiquettes()->count() > 0){
            $k = 0;
            foreach ($search->getEtiquettes() as $etiquette){
                $k++;
                $query = $query->andWhere(":etiquette$k MEMBER OF p.etiquettes")
                                ->setParameter("etiquette$k", $etiquette);
            }
        }

        return $query->getQuery();
    }

    /*
    public function findOneBySomeField($value): ?Picture
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

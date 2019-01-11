<?php

namespace App\Controller\Admin;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="admin_dashboard", methods="GET")
     * @param ObjectManager $em
     * @return Response
     */
    public function index(ObjectManager $em): Response
    {
        $users = $em->createQuery('SELECT COUNT(u) FROM App\Entity\User u')->getSingleScalarResult();
        $albums = $em->createQuery('SELECT COUNT(u) FROM App\Entity\Album u')->getSingleScalarResult();
        $projets = $em->createQuery('SELECT COUNT(u) FROM App\Entity\Projet u')->getSingleScalarResult();
        $photos = $em->createQuery('SELECT COUNT(u) FROM App\Entity\Picture u')->getSingleScalarResult();
        $bestPics = $em->createQuery(
            'SELECT AVG(c.rating) as note, p.title, p.id, p.slug, u.firstName, u.lastName, u.username, p.filename
             FROM App\Entity\Comment c
             JOIN c.picture p
             JOIN p.album a
             JOIN a.projet pr
             JOIN pr.author u
             GROUP BY p
             ORDER BY note DESC 
            '
        )->setMaxResults(5)
        ->getResult();
        $worstPics = $em->createQuery(
            'SELECT AVG(c.rating) as note, p.title, p.id, p.slug, u.firstName, u.lastName, u.username, p.filename
             FROM App\Entity\Comment c
             JOIN c.picture p
             JOIN p.album a
             JOIN a.projet pr
             JOIN pr.author u
             GROUP BY p
             ORDER BY note ASC 
            '
        )->setMaxResults(5)
            ->getResult();
        return $this->render('admin/dashboard.html.twig', compact('users', 'albums', 'projets', 'photos', 'bestPics', 'worstPics'));
    }

}

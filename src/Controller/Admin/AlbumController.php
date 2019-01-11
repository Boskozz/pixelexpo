<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\Projet;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/album")
 */
class AlbumController extends AbstractController
{
    /**
     * @Route("/", name="admin_album_index", methods="GET")
     * @param AlbumRepository $repo
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(AlbumRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $allAlbums = $paginator->paginate(
            $repo->findAllQuery(),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('admin/album/index.html.twig', [
            'albums' => $allAlbums
        ]);
    }

    /**
     * @Route("/new/{slug}", name="admin_album_new", methods="GET|POST")
     * @param Request $request
     * @param ObjectManager $em
     * @param Projet $projet
     * @return Response
     */
    public function new(Request $request, ObjectManager $em, Projet $projet): Response
    {
        $album = new Album();
        $album->setProjet($projet);
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($album);
            $em->flush();

            $this->addFlash('success', "Album bien enregistré");

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/new.html.twig', [
            'album' => $album,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="admin_album_show", methods="GET")
     * @param Album $album
     * @return Response
     */
    public function show(Album $album): Response
    {
        return $this->render('admin/album/show.html.twig', ['album' => $album]);
    }

    /**
     * @Route("/{id}/edit", name="admin_album_edit", methods="GET|POST")
     * @param Request $request
     * @param Album $album
     * @param ObjectManager $em
     * @return Response
     */
    public function edit(Request $request, Album $album, ObjectManager $em): Response
    {
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_album_show', ['slug' => $album->getSLug()]);
        }

        return $this->render('admin/album/edit.html.twig', [
            'album' => $album,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_album_delete", methods="DELETE")
     * @param Request $request
     * @param Album $album
     * @return Response
     */
    public function delete(Request $request, Album $album): Response
    {
        if ($this->isCsrfTokenValid('delete'.$album->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($album);
            $em->flush();
        }

        return $this->redirectToRoute('admin_album_index');
    }
}

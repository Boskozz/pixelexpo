<?php

namespace App\Controller;

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
 *
 * @Route("/album")
 */
class AlbumController extends AbstractController
{
    /**
     * @Route("/", name="album_index", methods="GET")  -->>> FindBySlug join Projet where slug == slug
     * @param AlbumRepository $repo
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(AlbumRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $albums = $paginator->paginate(
            $repo->findByProjetUserQuery($this->getUser()),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('album/index.html.twig', [
            'albums' => $albums
        ]);
    }

    /**
     * @Route("/new/{slug}", name="album_new", methods="GET|POST")
     * @param Request $request
     * @param ObjectManager $em
     * @param Projet $projet
     * @return Response
     * @throws \Exception
     */
    public function new(Request $request, ObjectManager $em, Projet $projet): Response
    {
        $album = new Album();
        $album->setProjet($projet);
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($album->getPictures() as $picture){
                if ($picture->getAutorizeFile() == false){
                    $this->addFlash('danger', "Les dimensions doivent être inférieur ou égale à 1920 x 1080 et inversément !");
                    return $this->redirectToRoute('album_new', ['slug' => $projet->getSlug() ]);
                }
            }

            $em->persist($album);
            $em->flush();

            $this->addFlash('success', "Album bien enregistré");

            return $this->redirectToRoute('album_index');
        }

        return $this->render('album/new.html.twig', [
            'projet' => $projet,
            'album' => $album,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/{slug}", name="album_show", methods="GET")
     * @param AlbumRepository $repo
     * @param $id
     * @param $slug
     * @return Response
     */
    public function show(AlbumRepository $repo, $slug, $id): Response
    {
        $album = $repo->find($id);
        //dump($slug);die();
        if ($album->getSlug() !== $slug) {
            return $this->redirectToRoute('album_show', [
                'id'   => $album->getId(),
                'slug' => $album->getSlug()
            ], 301);
        }
        return $this->render('album/show.html.twig', ['album' => $album]);
    }

    /**
     * @Route("/{id}/{slug}/edit", name="album_edit", methods="GET|POST")
     * @param Request $request
     * @param Album $album
     * @param ObjectManager $em
     * @return Response
     * @throws \Exception
     */
    public function edit(Request $request, Album $album, ObjectManager $em): Response
    {

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($album->getPictures() as $picture){
                if ($picture->getAutorizeFile() == false){
                    $this->addFlash('danger', "Les dimensions doivent être inférieur ou égale à 1920 x 1920 !");
                    return $this->redirectToRoute('album_edit', ['id' => $album->getId(), 'slug' => $album->getSlug() ]);
                }
            }
            $em->flush();
            return $this->redirectToRoute('album_show', ['id' => $album->getId(), 'slug' => $album->getSlug()]);
        }

        return $this->render('album/edit.html.twig', [
            'album' => $album,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="album_delete", methods="DELETE")
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

        return $this->redirectToRoute('album_index');
    }
}

<?php

namespace App\Controller;

use App\Entity\ImageSearch;
use App\Entity\Picture;
use App\Form\ImageSearchType;
use App\Repository\AlbumRepository;
use App\Repository\PictureRepository;
use App\Repository\ProjetRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     * @param PictureRepository $repo
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(PictureRepository $repo, Request $request, PaginatorInterface $paginator)
    {
        $search = new ImageSearch();
        $form = $this->createForm(ImageSearchType::class, $search);
        $form->handleRequest($request);
        $picturesPublic = $paginator->paginate(
            $repo->findAllPublicQuery($search),
            $request->query->getInt('page', 1),
            22
            );
        return $this->render('home/index.html.twig', [
            'pictures' => $picturesPublic,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/photo/{slug}-{id}", name="image_public")
     * @param Picture $picture
     * @param string $slug
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Picture $picture, string $slug){
        if ($picture->getSlug() !== $slug) {
            return $this->redirectToRoute('image_public', [
                'id'   => $picture->getId(),
                'slug' => $picture->getSlug()
            ], 301);
        }

        return $this->render('home/image.html.twig', [
            'picture' => $picture
        ]);
    }

    /**
     * @Route("/faq", name="faq_public")
     */
    public function tutoFaq() {
        return $this->render('home/faq.html.twig');
    }

    /**
     * @Route("/gestion", name="gestion_complete")
     * @param PictureRepository $repoPi
     * @param AlbumRepository $repoAl
     * @param ProjetRepository $repoPr
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function gestionComplete(PictureRepository $repoPi , AlbumRepository $repoAl, ProjetRepository $repoPr, PaginatorInterface $paginator, Request $request) {
        // Projet
        $mesProjets = $paginator->paginate(
            $repoPr->findByUserQuery($this->getUser()),
            $request->query->getInt('projet', 1),
            10
        );
        // Album
        $albums = $paginator->paginate(
            $repoAl->findByProjetUserQuery($this->getUser()),
            $request->query->getInt('album', 1),
            10
        );
        $user = $this->getUser();
        return $this->render('home/gestion.html.twig', [
            'projets' => $mesProjets,
            'albums'  => $albums,
            'user' => $user
        ]);
    }
}

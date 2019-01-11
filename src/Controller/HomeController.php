<?php

namespace App\Controller;

use App\Entity\ImageSearch;
use App\Entity\Picture;
use App\Form\ImageSearchType;
use App\Repository\PictureRepository;
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
            23
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
}

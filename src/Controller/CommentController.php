<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    private $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/mes-commentaires", name="comment_mes_com")
     * @param CommentRepository $repo
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mesCommentaires(CommentRepository $repo, Request $request)
    {
        $mesCom = $this->paginator->paginate(
            $repo->findByAuthorLimit($this->getUser(), 0),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('comment/mesCom.html.twig', [
            'mesCom' => $mesCom,
        ]);
    }

    /**
     * @IsGranted("ROLE_CREATOR")
     * @Route("/commentaires-photo", name="comment_mes_photo")
     * @param CommentRepository $repo
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mesPhotosCommentaires(CommentRepository $repo, Request $request)
    {
        $mesPhotos = $this->paginator->paginate(
            $repo->findMesPhotosComment($this->getUser(), 0),
            $request->query->getInt('page', 1),
            20
        );
        return $this->render('comment/mesPhotos.html.twig', [
            'comments' => $mesPhotos
        ]);
    }
}

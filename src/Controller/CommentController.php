<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\ReponseType;
use App\Repository\CommentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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

    /**
     * @IsGranted("ROLE_CREATOR")
     * @Route("/reponse-commentaire/{id}", name="reponse-comm")
     * @param Comment $comment
     * @param Request $request
     * @param ObjectManager $em
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function repondreAMesComm(Comment $comment, Request $request, ObjectManager $em) {
        $commentAuthor = $comment->getPicture()->getAlbum()->getProjet()->getAuthor();
        if ($commentAuthor === $this->getUser()) {
            $form = $this->createForm(ReponseType::class, $comment);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()){
                $em->persist($comment);
                $em->flush();
                $this->addFlash('success', "Réponse au commentaire bien enregistrée");
                return $this->redirectToRoute('public_picture', ['id' => $comment->getPicture()->getId(),
                    'slug' => $comment->getPicture()->getSlug(), 'username' => $commentAuthor->getUsername() ]);
            }
            return $this->render('comment/reponseComm.html.twig', [
                'form' => $form->createView(),
                'comment' => $comment
            ]);
        }
    }
}

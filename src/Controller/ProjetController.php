<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/projet")
 */
class ProjetController extends AbstractController
{
    /**
     * @Route("/", name="projet_index", methods="GET")
     * @param ProjetRepository $repo
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(ProjetRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $mesProjets = $paginator->paginate(
            $repo->findByUserQuery($this->getUser()),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('projet/index.html.twig', [
            'projets' => $mesProjets
            ]);
    }

    /**
     * @Route("/new", name="projet_new", methods="GET|POST")
     * @param Request $request
     * @param ObjectManager $em
     * @return Response
     * @throws \Exception
     */
    public function new(Request $request, ObjectManager $em): Response
    {
        $projet = new Projet();
        $projet->setAuthor($this->getUser());
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUser()->setRefresh(new \DateTime());
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', "Projet bien enregistré");

            return $this->redirectToRoute('gestion_complete');
        }

        return $this->render('projet/new.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="projet_show", methods="GET")
     * @param Projet $projet
     * @return Response
     */
    public function show(Projet $projet): Response
    {
        return $this->render('projet/show.html.twig', ['projet' => $projet]);
    }

    /**
     * @Route("/{id}/edit", name="projet_edit", methods="GET|POST")
     * @param Request $request
     * @param Projet $projet
     * @return Response
     * @throws \Exception
     */
    public function edit(Request $request, Projet $projet): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUser()->setRefresh(new \DateTime());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gestion_complete');
        }

        return $this->render('projet/edit.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="projet_delete", methods="DELETE")
     * @param Request $request
     * @param Projet $projet
     * @return Response
     * @throws \Exception
     */
    public function delete(Request $request, Projet $projet): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projet->getId(), $request->request->get('_token'))) {
            $this->getUser()->setRefresh(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->remove($projet);
            $em->flush();
        }

        return $this->redirectToRoute('gestion_complete');
    }
}

<?php

namespace App\Controller\Admin;

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
 * @Route("/admin/projet")
 */
class ProjetController extends AbstractController
{
    /**
     * @Route("/", name="admin_projet_index", methods="GET")
     * @param ProjetRepository $repo
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(ProjetRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $allProjets = $paginator->paginate(
            $repo->findAllQuery(),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('admin/projet/index.html.twig', ['projets' => $allProjets]);
    }

    /**
     * @Route("/new", name="admin_projet_new", methods="GET|POST")
     * @param Request $request
     * @param ObjectManager $em
     * @return Response
     * @throws \Exception
     */
    public function new(Request $request, ObjectManager $em): Response
    {
        $projet = new Projet();
        $projet->setAuthor($this->getUser()); // Ajout de l'utilisateur dans le nouveau projet
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', "Projet bien enregistré");

            return $this->redirectToRoute('admin_projet_index');
        }

        return $this->render('admin/projet/new.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="admin_projet_show", methods="GET")
     * @param Projet $projet
     * @return Response
     */
    public function show(Projet $projet): Response
    {
        return $this->render('admin/projet/show.html.twig', ['projet' => $projet]);
    }

    /**
     * @Route("/{slug}/edit", name="admin_projet_edit", methods="GET|POST")
     * @param Request $request
     * @param Projet $projet
     * @return Response
     */
    public function edit(Request $request, Projet $projet): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_projet_index', ['id' => $projet->getId()]);
        }

        return $this->render('admin/projet/edit.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_projet_delete", methods="DELETE")
     * @param Request $request
     * @param Projet $projet
     * @return Response
     */
    public function delete(Request $request, Projet $projet): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projet->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($projet);
            $em->flush();
        }

        return $this->redirectToRoute('admin_projet_index');
    }
}

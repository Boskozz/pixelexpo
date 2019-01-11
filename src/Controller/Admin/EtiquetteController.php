<?php

namespace App\Controller\Admin;

use App\Entity\Etiquette;
use App\Form\EtiquetteType;
use App\Repository\EtiquetteRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/etiquette")
 */
class EtiquetteController extends AbstractController
{
    /**
     * @Route("/", name="admin_etiquette_index", methods="GET")
     * @param EtiquetteRepository $repo
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(EtiquetteRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $allEtiquettes = $paginator->paginate(
            $repo->findAllQuery(),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('admin/etiquette/index.html.twig', ['etiquettes' => $allEtiquettes]);
    }

    /**
     * @Route("/new", name="admin_etiquette_new", methods="GET|POST")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $etiquette = new Etiquette();
        $form = $this->createForm(EtiquetteType::class, $etiquette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($etiquette);
            $em->flush();

            return $this->redirectToRoute('admin_etiquette_index');
        }

        return $this->render('admin/etiquette/new.html.twig', [
            'etiquette' => $etiquette,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_etiquette_show", methods="GET")
     * @param Etiquette $etiquette
     * @return Response
     */
    public function show(Etiquette $etiquette): Response
    {
        return $this->render('admin/etiquette/show.html.twig', ['etiquette' => $etiquette]);
    }

    /**
     * @Route("/{id}/edit", name="admin_etiquette_edit", methods="GET|POST")
     * @param Request $request
     * @param Etiquette $etiquette
     * @return Response
     */
    public function edit(Request $request, Etiquette $etiquette): Response
    {
        $form = $this->createForm(EtiquetteType::class, $etiquette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('etiquette_index', ['id' => $etiquette->getId()]);
        }

        return $this->render('admin/etiquette/edit.html.twig', [
            'etiquette' => $etiquette,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_etiquette_delete", methods="DELETE")
     * @param Request $request
     * @param Etiquette $etiquette
     * @return Response
     */
    public function delete(Request $request, Etiquette $etiquette): Response
    {
        if ($this->isCsrfTokenValid('delete'.$etiquette->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($etiquette);
            $em->flush();
        }

        return $this->redirectToRoute('admin_etiquette_index');
    }
}

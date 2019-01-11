<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Form\PictureType;
use App\Repository\PictureRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/image")
 */
class PictureController extends AbstractController
{
    /**
     * @Route("/", name="picture_index")
     * @param PictureRepository $repo
     * @return Response
     */
    public function index(PictureRepository $repo){
        return $this->render('picture/index.html.twig', [
            'pictures' => $repo->findByPictureUser($this->getUser()),
            'portrait' => $repo->findGalerieUser($this->getUser(), 'portrait'),
            'paysage' => $repo->findGalerieUser($this->getUser(), 'paysage')
        ]);
    }

    /**
     * @Route("/{id}/{slug}", name="picture_show", methods="GET")
     * @param Picture $picture
     * @return Response
     */
    public function show(Picture $picture, $slug): Response
    {
        if ($picture->getSlug() !== $slug) {
            return $this->redirectToRoute('picture_show', [
                'id'   => $picture->getId(),
                'slug' => $picture->getSlug()
            ], 301);
        }
        return $this->render('picture/show.html.twig', ['picture' => $picture]);
    }

    /**
     * @Route("/{id}/{slug}/edit", name="picture_edit", methods="GET|POST")
     * @param Request $request
     * @param Picture $picture
     * @param ObjectManager $em
     * @return Response
     * @throws \Exception
     */
    public function edit(Request $request, Picture $picture, ObjectManager $em, $slug): Response
    {
        if ($picture->getSlug() !== $slug) {
            return $this->redirectToRoute('picture_show', [
                'id'   => $picture->getId(),
                'slug' => $picture->getSlug()
            ], 301);
        }
        if ($picture->getTitle() == "Photo"){
            $picture->setTitle("");
        }
        $picture->setImageFile(new File("./media/images/".$picture->getFilename() ));
        $form = $this->createForm(PictureType::class, $picture);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($picture->getTitle() != "Photo" and !empty($picture->getEtiquettes())){
                $em->flush();
                return $this->redirectToRoute('picture_show', ['slug' => $picture->getSlug(), 'id' => $picture->getId()]);
            }
        }

        return $this->render('picture/edit.html.twig', [
            'picture' => $picture,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="picture_delete", methods="DELETE")
     * @param Request $request
     * @param Picture $picture
     * @return Response
     */
    public function delete(Request $request, Picture $picture): Response
    {
       $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('delete' . $picture->getId(), $data['_token'])) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($picture);
            $em->flush();
            $this->addFlash('success', "Image supprimée correctement");
            return new JsonResponse(['success' => 1]);
        }
        return new JsonResponse(['error' => 'Token invalide'], 400);
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Picture;
use App\Form\PictureType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/image")
 */
class PictureController extends AbstractController
{

    /**
     * @Route("/{id}", name="admin_picture_show", methods="GET")
     * @param Picture $picture
     * @return Response
     */
    public function show(Picture $picture): Response
    {
        return $this->render('admin/picture/show.html.twig', ['picture' => $picture]);
    }

    /**
     * @Route("/{id}/edit", name="admin_picture_edit", methods="GET|POST")
     * @param Request $request
     * @param Picture $picture
     * @param ObjectManager $em
     * @return Response
     * @throws \Exception
     */
    public function edit(Request $request, Picture $picture, ObjectManager $em): Response
    {
        $picture->setImageFile(new File("./media/images/".$picture->getFilename() ));
        $form = $this->createForm(PictureType::class, $picture);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_picture_show', ['id' => $picture->getId()]);
        }

        return $this->render('admin/picture/edit.html.twig', [
            'picture' => $picture,
            'form' => $form->createView(),
        ]);
    }

}

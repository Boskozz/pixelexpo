<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Form\PictureType;
use App\Repository\PictureRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
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
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(PictureRepository $repo, Request $request, PaginatorInterface $paginator){
        $pictures = $paginator->paginate(
            $repo->findByPictureUserQuery($this->getUser()),
            $request->query->getInt('page', 1),
            10
        );
        $portrait = $paginator->paginate(
            $repo->findGalerieUserQuery($this->getUser(), "portrait"),
            $request->query->getInt('page', 1),
            10
        );
        $paysage = $paginator->paginate(
            $repo->findGalerieUserQuery($this->getUser(), "paysage"),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('picture/index.html.twig', [
            'pictures' => $pictures,
            'portrait' => $portrait,
            'paysage' => $paysage
        ]);
    }
//
//    /**
//     * Serve a file by forcing the download
//     *
//     * @Route("/download/{filename}", name="download_file", requirements={"filename": ".+"})
//     */
//    public function downloadFileAction($filename)
//    {
//        /**
//         * $basePath can be either exposed (typically inside web/)
//         * or "internal"
//         */
//        $basePath = $this->container->getParameter('kernel.root_dir').'/Resources/my_custom_folder';
//
//        $filePath = $basePath.'/'.$filename;
//
//        // check if file exists
//        //$fs = new FileSystem();
//        if (!$fs->exists($filePath)) {
//            throw $this->createNotFoundException();
//        }
//
//        // prepare BinaryFileResponse
//        $response = new BinaryFileResponse($filePath);
//        $response->trustXSendfileTypeHeader();
//        $response->setContentDisposition(
//            ResponseHeaderBag::DISPOSITION_INLINE,
//            $filename,
//            iconv('UTF-8', 'ASCII//TRANSLIT', $filename)
//        );
//
//        return $response;
//    }

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
                $this->getUser()->setRefresh(new \DateTime());
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
     * @throws \Exception
     */
    public function delete(Request $request, Picture $picture): Response
    {
       $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('delete' . $picture->getId(), $data['_token'])) {
            $em = $this->getDoctrine()->getManager();
            $this->getUser()->setRefresh(new \DateTime());
            $em->remove($picture);
            $em->flush();
            $this->addFlash('success', "Image supprimée correctement");
            return new JsonResponse(['success' => 1]);
        }
        return new JsonResponse(['error' => 'Token invalide'], 400);
    }
}

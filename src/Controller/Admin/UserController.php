<?php
/**
 * Created by PhpStorm.
 * User: Utilisateur
 * Date: 16-12-18
 * Time: 02:18
 */

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminPassType;
use App\Form\UserAdminType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserController
 * @package App\Controller\Admin
 * @Route("/admin/users")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="admin_user_index")
     * @param UserRepository $repo
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(UserRepository $repo, PaginatorInterface $paginator, Request $request)
    {
        $users = $paginator->paginate(
            $repo->findAllQuery(),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('admin/user/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/{username}/edit", name="admin_user_edit")
     * @param User $user
     * @param Request $request
     * @param ObjectManager $em
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function edit(User $user, Request $request, ObjectManager $em){
        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid() ){
            $em->flush();
            $this->addFlash('success', "Utilisateur mis à jour");
            $this->redirectToRoute('admin_user_index');
        }
        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{username}/change-pass", name="admin_user_pass")
     * @param User $user
     * @param Request $request
     * @param ObjectManager $em
     * @return Response
     */
    public function changePass(User $user, Request $request, ObjectManager $em, UserPasswordEncoderInterface $encoder) {
        $form = $this->createForm(AdminPassType::class, $user);
        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid() ){
            $newPass = $user->getPassword();
            $password = $encoder->encodePassword($user, $newPass);
            $user->setPassword($password);
            $em->flush();
            $this->addFlash('success', "Mot de passe mis à jour");
            $this->redirectToRoute('admin_user_index');
        }
        return $this->render('admin/user/pass.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
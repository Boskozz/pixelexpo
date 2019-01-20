<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\PasswordUpdate;
use App\Entity\Picture;
use App\Entity\User;
use App\Events;
use App\Form\CommentType;
use App\Form\PasswordUpdType;
use App\Form\RegistrationType;
use App\Repository\AlbumRepository;
use App\Repository\CommentRepository;
use App\Repository\PictureRepository;
use App\Repository\ProjetRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $authenticationUtils
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'inscription
     * @Route("/register", name="register")
     * @param Request $request
     * @param ObjectManager $em
     * @param UserPasswordEncoderInterface $encoder
     * @param EventDispatcherInterface $eventDispatcher
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request, ObjectManager $em, UserPasswordEncoderInterface $encoder, EventDispatcherInterface $eventDispatcher){
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $password = $encoder->encodePassword($user,$user->getPassword());
            $user->setPassword($password);
            $em->persist($user);
            $em->flush();

            $event = new GenericEvent($user);
            $eventDispatcher->dispatch(Events::USER_REGISTERED, $event);

            $this->addFlash('success', "Bien enregistré");
            return $this->redirectToRoute('login');
        }
        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/update-password", name="update_password")
     * @param Request $request
     * @param ObjectManager $em
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updatePassword(Request $request, ObjectManager $em, UserPasswordEncoderInterface $encoder) {
        $passwordUpdate = new PasswordUpdate();
        $user = $this->getUser();
        $form = $this->createForm(PasswordUpdType::class, $passwordUpdate);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            if (!password_verify($passwordUpdate->getOldPassword(), $user->getPassword())){
                $form->get('oldPassword')->addError(new FormError("Votre présent mot de passe ne correspond pas à votre mot de passe actuel !"));
            } else {
                $newPass = $passwordUpdate->getNewPassword();
                $password = $encoder->encodePassword($user, $newPass);
                $user->setPassword($password);
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', "Mot de passe bien modifié");
                $this->redirectToRoute('public_profile', ['username' => $user->getUsername()]);
            }
        }
        return $this->render('security/password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param AlbumRepository $repo
     * @param CommentRepository $repoCom
     * @param ObjectManager $em
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/tableau-de-bord", name="user_dashboard")
     */
    public function dashboard(AlbumRepository $repo, CommentRepository $repoCom, ObjectManager $em){
        $user = $this->getUser();
        $mesProjets = $repo->findMesProjets($user);
        $mesComments = $repoCom->findByAuthorLimit($user, 5);
        $mesPhotosComment = $repoCom->findMesPhotosComment($user, 5);
        $bestPics = $em->createQuery(
            'SELECT AVG(c.rating) as note, p.title, p.id, p.slug, u.firstName, u.lastName, u.username, p.filename
             FROM App\Entity\Comment c
             JOIN c.picture p
             JOIN p.album a
             JOIN a.projet pr
             JOIN pr.author u
             GROUP BY p
             ORDER BY note DESC 
            '
        )->setMaxResults(5)
            ->getResult();
        $worstPics = $em->createQuery(
            'SELECT AVG(c.rating) as note, p.title, p.id, p.slug, u.firstName, u.lastName, u.username, p.filename
             FROM App\Entity\Comment c
             JOIN c.picture p
             JOIN p.album a
             JOIN a.projet pr
             JOIN pr.author u
             GROUP BY p
             ORDER BY note ASC 
            '
        )->setMaxResults(5)
            ->getResult();
        //dump($mesProjets);die();
        return $this->render('security/dashboard.html.twig',
            compact('user', 'mesProjets', 'mesComments', 'mesPhotosComment', 'bestPics', 'worstPics'));
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/{username}/", name="public_profile")
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profilPublic(User $user) {
        return $this->render('security/profile.html.twig', compact('user'));
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/{username}/projets", name="public_projet")
     * @param User $user
     * @param ProjetRepository $repo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function projetPublic(User $user, ProjetRepository $repo) {
        $projetsPublic = $repo->findByUserPublic($user);
        return $this->render('security/projets.html.twig', ['projets' => $projetsPublic, 'user' => $user]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/{username}/album/{id}/{slug}", name="public_album")
     * @param AlbumRepository $repo
     * @param string $slug
     * @param PictureRepository $pictureRepository
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function albumPublic(AlbumRepository $repo, string $slug, PictureRepository $pictureRepository, $username) {
        $albumPublic = $repo->findBySlugPublic($slug);
        $pictures = '';
        if (empty($albumPublic)) {
            $this->addFlash('warning', "Cet album est privé !");
            return $this->redirectToRoute('homepage');
        } else {
            $pictures = $pictureRepository->findBy(['album' => $albumPublic, 'private' => false]);
        }

        $user = $albumPublic->getProjet()->getAuthor();
        if ($albumPublic->getSlug() !== $slug or $albumPublic->getProjet()->getAuthor()->getUsername() != $username ) {
            return $this->redirectToRoute('public_album', [
                'id'   => $albumPublic->getId(),
                'slug' => $albumPublic->getSlug(),
                'username' => $albumPublic->getProjet()->getAuthor()->getUsername()
            ], 301);
        }
        return $this->render('security/albums.html.twig', ['album' => $albumPublic, 'user' => $user, 'pictures' => $pictures]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/{username}/image/{id}/{slug}", name="public_picture")
     * @param Picture $picture
     * @param string $slug
     * @param string $username
     * @param Request $request
     * @param ObjectManager $em
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function picturePublic(Picture $picture, string $slug, string $username, Request $request, ObjectManager $em) {
        if ($picture->getSlug() !== $slug or $picture->getAlbum()->getProjet()->getAuthor()->getUsername() !== $username) {
            return $this->redirectToRoute('public_picture', [
                'id'   => $picture->getId(),
                'slug' => $picture->getSlug(),
                'username' => $picture->getAlbum()->getProjet()->getAuthor()->getUsername()
            ], 301);
        }
        $comment = new Comment();
        $comment->setPicture($picture)
                ->setAuthor($this->getUser());
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid() && empty($picture->getCommentFromAuthor($this->getUser()))) {
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', "Commentaire bien enregistré");
            return $this->redirectToRoute('public_picture', [
                'id'   => $picture->getId(),
                'slug' => $picture->getSlug(),
                'username' => $username
            ]);
        }
        return $this->render('security/picture.html.twig', ['picture' => $picture, 'form' => $form->createView()]);
    }
}

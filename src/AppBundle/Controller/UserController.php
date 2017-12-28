<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UserController extends Controller
{
    /**
     * @Route("/user/register", name="user_register")
     * @param Request $request
     * @return Response
     */
    public function register(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $roleRepo = $this->getDoctrine()->getManager()->getRepository(Role::class);
            $userRole = $roleRepo->findOneBy(['name' => 'ROLE_USER']);

            $user->addRole($userRole);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('security_login');
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/profile", name="user_profile")
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     */
    public function viewProfile()
    {
        $user = $this->getUser();

        return $this->render('user/profile.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/user/admin/{id}", name="user_admin_make")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function makeAdmin(int $id)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($id);

        if ($user === null)
        {
            return $this->redirectToRoute('home_page');
        }

        if ($user->isAdmin()){
            return $this->redirectToRoute('home_page');
        }

        $em = $this->getDoctrine()->getManager();

        $roleRepo = $this->getDoctrine()->getRepository(Role::class);
        $role = $roleRepo->findOneBy(['name' => 'ROLE_ADMIN']);
        $user->addRole($role);

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('admin_home');
    }
}

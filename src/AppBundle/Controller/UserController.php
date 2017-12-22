<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
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
        $errors = [];
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $this->validateProccess($errors, $user);

            if (count($errors) > 0){
                return $this->render('user/register.html.twig', [
                    'form' => $form->createView(),
                    'errors' => $errors
                ]);
            }

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
            'form' => $form->createView(),
            'errors' => $errors
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
     * Helper Methods
     */
    public function isValidEmail($str)
    {
        return preg_match(
            "/^[a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/", $str);
    }

    public function isValidLength(?string $str, int $minLength, int $maxLength)
    {
        return mb_strlen($str) >= $minLength && mb_strlen($str) <= $maxLength;
    }

    public function userExists($criteria, $property)
    {
        $repo = $this->getDoctrine()->getRepository(User::class);
        return $repo->findBy([$criteria => $property]);
    }

    public function validateProccess(array &$errors, User $user)
    {
        if (!$this->isValidEmail($user->getEmail())){
            $errors[] = 'That is not a valid email!';
        }

        if ($this->userExists('email', $user->getEmail())){
            $errors[] = 'There is an user with this email! Do you already have an account?';
        }

        if (!$this->isValidLength($user->getFullName(), 4, 255)){
            $errors[] = 'Your full name must be between 4 and 255 characters long!';
        }

        if (!$this->isValidLength($user->getPassword(), 3, 255 )){
            $errors[] = 'Your password must be between 3 and 255 characters long!';
        }
    }
}

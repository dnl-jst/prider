<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\Translator;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="user_index")
     */
    public function indexAction(EntityManagerInterface $entityManager)
    {
        /** @var User $users */
        $users = $entityManager->getRepository('AppBundle:User')->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/add", name="user_add")
     */
    public function addAction(Request $request, EntityManagerInterface $entityManager, Translator $translator)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('user_index');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$user->getPlainPassword()) {
                $form->addError(new FormError($translator->trans('Please enter a password.')));
            }

            if (!$form->getErrors()->count()) {
                $password = $this->get('security.password_encoder')
                                 ->encodePassword($user, $user->getPlainPassword());

                $user->setPassword($password);

                $this->addFlash('success', $translator->trans(
                    'User "%name%" successfully created.',
                    ['%name%' => $user->getName()]
                ));

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('user_index');
            }
        }

        return $this->render('user/form.html.twig', [
            'title' => 'Create user',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit")
     */
    public function editAction(
        Request $request,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        Translator $translator,
        $id
    ) {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('user_index');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var User $user */
        $user = $entityManager->getRepository('AppBundle:User')->findOneBy(['id' => $id]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getPlainPassword()) {
                $password = $this->get('security.password_encoder')
                                 ->encodePassword($user, $user->getPlainPassword());

                $user->setPassword($password);
            }

            $this->addFlash('success', $translator->trans(
                'User "%name%" successfully updated.',
                ['%name%' => $user->getName()]
            ));

            $entityManager->flush();

            if ($user === $currentUser) {
                $session->set('_locale', $user->getLocale());
            }

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/form.html.twig', [
            'title' => 'Edit user',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name="user_delete")
     */
    public function deleteAction(Request $request, EntityManagerInterface $entityManager, Translator $translator, $id)
    {
        /** @var User $user */
        $user = $entityManager->getRepository('AppBundle:User')->findOneBy(['id' => $id]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('post')) {
            if (!$request->get('cancel')) {
                $entityManager->remove($user);
                $entityManager->flush();

                $this->addFlash('success', $translator->trans(
                    'User "%name%" successfully deleted.',
                    ['%name%' => $user->getName()]
                ));
            }

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'delete-form.html.twig',
            array(
                'headline' => $translator->trans('Really delete user?'),
                'text' => $translator->trans('Are you really sure you want to delete this user?'),
                'entityTitle' => $translator->trans('User name: %name%', ['%name%' => $user->getName()])
            )
        );
    }

}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use AppBundle\Form\GroupType;
use AppBundle\Form\UserType;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="user_index")
     */
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $users */
        $users = $em->getRepository('AppBundle:User')->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/add", name="user_add")
     */
    public function addAction(Request $request)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('user_index');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$user->getPlainPassword()) {
                $form->addError(new FormError('Bitte vergeben Sie ein Passwort.'));
            }

            if (!$form->getErrors()->count()) {

                $password = $this->get('security.password_encoder')
                                 ->encodePassword($user, $user->getPlainPassword());

                $user->setPassword($password);

                $this->addFlash('success', 'Benutzer "' . $user->getEmail() . '" wurde erfolgreich angelegt.');

                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('user_index');
            }
        }

        return $this->render('user/form.html.twig', [
            'title' => 'Benutzer erstellen',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="user_edit")
     */
    public function editAction(Request $request, $id)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('user_index');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->findOneBy(['id' => $id]);

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

            $this->addFlash('success', 'Benutzer "' . $user->getEmail() . '" wurde erfolgreich gespeichert.');

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/form.html.twig', [
            'title' => 'Benutzer bearbeiten',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="user_delete")
     */
    public function deleteAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->findOneBy(['id' => $id]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('post')) {

            if (!$request->get('cancel')) {
                $em->remove($user);
                $em->flush();

                $this->addFlash('success', 'Benutzer "' . $user->getEmail() . '" wurde gelöscht.');
            }

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'delete-form.html.twig',
            array(
                'headline' => 'Benutzer wirklich löschen?',
                'text' => 'Sind Sie sicher, dass Sie den Benutzer wirklich löschen möchten?',
                'entityTitle' => 'Benutzer-Name: "' . $user->getEmail() . '"'
            )
        );
    }

}

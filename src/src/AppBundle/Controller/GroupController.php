<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Form\GroupType;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/group")
 */
class GroupController extends Controller
{
    /**
     * @Route("/", name="group_index")
     */
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Group $groups */
        $groups = $em->getRepository('AppBundle:Group')->findAll();

        return $this->render('group/index.html.twig', [
            'groups' => $groups
        ]);
    }

    /**
     * @Route("/add", name="group_add")
     */
    public function addAction(Request $request)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('group_index');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash('success', 'Gruppe "' . $group->getName() . '" wurde erfolgreich angelegt.');

            $em->persist($group);
            $em->flush();

            return $this->redirectToRoute('group_index');
        }

        return $this->render('group/form.html.twig', [
            'title' => 'Gruppe erstellen',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="group_edit")
     */
    public function editAction(Request $request, $id)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('group_index');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Group $group */
        $group = $em->getRepository('AppBundle:Group')->findOneBy(['id' => $id]);

        if (!$group) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash('success', 'Server "' . $group->getName() . '" wurde erfolgreich gespeichert.');

            $em->persist($group);
            $em->flush();

            return $this->redirectToRoute('group_index');
        }

        return $this->render('group/form.html.twig', [
            'title' => 'Gruppe bearbeiten',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="group_delete")
     */
    public function deleteAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Group $group */
        $group = $em->getRepository('AppBundle:Group')->findOneBy(['id' => $id]);

        if (!$group) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('post')) {

            if (!$request->get('cancel')) {
                $em->remove($group);
                $em->flush();

                $this->addFlash('success', 'Gruppe "' . $group->getName() . '" wurde gelöscht.');
            }

            return $this->redirectToRoute('group_index');
        }

        return $this->render(
            'delete-form.html.twig',
            array(
                'headline' => 'Gruppe wirklich löschen?',
                'text' => 'Sind Sie sicher, dass Sie den Gruppe wirklich löschen möchten?',
                'entityTitle' => 'Gruppen-Name: "' . $group->getName() . '"'
            )
        );
    }

}

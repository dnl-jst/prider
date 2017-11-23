<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Form\GroupType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Translator;

/**
 * @Route("/group")
 */
class GroupController extends Controller
{
    /**
     * @Route("/", name="group_index")
     */
    public function indexAction(EntityManagerInterface $entityManager)
    {
        /** @var Group $groups */
        $groups = $entityManager->getRepository(Group::class)->findAll();

        return $this->render('group/index.html.twig', [
            'groups' => $groups
        ]);
    }

    /**
     * @Route("/add", name="group_add")
     */
    public function addAction(Request $request, EntityManagerInterface $entityManager, Translator $translator)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('group_index');
        }

        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash(
                'success',
                $translator->trans('Group "%name%" was created.', ['name' => $group->getName()])
            );

            $entityManager->persist($group);
            $entityManager->flush();

            return $this->redirectToRoute('group_index');
        }

        return $this->render('group/form.html.twig', [
            'title' => 'Create group',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="group_edit")
     */
    public function editAction(Request $request, EntityManagerInterface $entityManager, Translator $translator, $id)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('group_index');
        }

        /** @var Group $group */
        $group = $entityManager->getRepository(Group::class)->findOneBy(['id' => $id]);

        if (!$group) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash(
                'success',
                $translator->trans('Group "%name%" was updated.', ['%name%' => $group->getName()])
            );

            $entityManager->flush();

            return $this->redirectToRoute('group_index');
        }

        return $this->render('group/form.html.twig', [
            'title' => 'Edit group',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name="group_delete")
     */
    public function deleteAction(Request $request, EntityManagerInterface $entityManager, Translator $translator, $id)
    {
        /** @var Group $group */
        $group = $entityManager->getRepository(Group::class)->findOneBy(['id' => $id]);

        if (!$group) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('post')) {
            if (!$request->get('cancel')) {
                $entityManager->remove($group);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    $translator->trans('Group "%name%" was deleted.', ['%name%' => $group->getName()])
                );
            }

            return $this->redirectToRoute('group_index');
        }

        return $this->render(
            'delete-form.html.twig',
            array(
                'headline' => $translator->trans('Really delete group?'),
                'text' => $translator->trans('Are you really sure you want to delete this group?'),
                'entityTitle' => $translator->trans('Group name: %name%', ['%name%' => $group->getName()])
            )
        );
    }

}

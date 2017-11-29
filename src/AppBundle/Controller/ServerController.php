<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Entity\Server;
use AppBundle\Form\ServerType;
use AppBundle\Util\UpdateChecker;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Translator;

/**
 * @Route("/server")
 */
class ServerController extends Controller
{
    /**
     * @Route("/", name="server_index")
     */
    public function indexAction(EntityManagerInterface $entityManager)
    {
        /** @var Group $groups */
        $groups = $entityManager
            ->createQuery('SELECT g, s FROM AppBundle:Group g JOIN g.servers s ORDER BY g.name ASC, s.name ASC')
            ->getResult();

        /** @var Server $ungroupedServers */
        $ungroupedServers = $entityManager
            ->createQuery('SELECT s FROM AppBundle:Server s WHERE s.group IS NULL ORDER BY s.name ASC')
            ->getResult();

        return $this->render('server/index.html.twig', [
            'groups' => $groups,
            'ungroupedServers' => $ungroupedServers
        ]);
    }

    /**
     * @Route("/add", name="server_add")
     */
    public function addAction(Request $request, EntityManagerInterface $entityManager)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('server_index');
        }

        $server = new Server();
        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Server was created.');

            $entityManager->persist($server);
            $entityManager->flush();

            return $this->redirectToRoute('server_index');
        }

        return $this->render('server/form.html.twig', [
            'title' => 'Create server',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="server_edit")
     */
    public function editAction(Request $request, EntityManagerInterface $entityManager, $id)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('server_index');
        }

        /** @var Server $server */
        $server = $entityManager->getRepository('AppBundle:Server')->findOneBy(['id' => $id]);

        if (!$server) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Server was updated.');

            $entityManager->flush();

            return $this->redirectToRoute('server_index');
        }

        return $this->render('server/form.html.twig', [
            'title' => 'Edit server',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name="server_delete")
     */
    public function deleteAction(Request $request, EntityManagerInterface $entityManager, Translator $translator, $id)
    {
        /** @var Server $server */
        $server = $entityManager->getRepository('AppBundle:Server')->findOneBy(['id' => $id]);

        if (!$server) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('post')) {
            if (!$request->get('cancel')) {
                $entityManager->remove($server);
                $entityManager->flush();

                $this->addFlash('success', 'Server was deleted.');
            }

            return $this->redirectToRoute('server_index');
        }

        return $this->render(
            'delete-form.html.twig',
            array(
                'headline' => $translator->trans('Really delete server?'),
                'text' => $translator->trans('Are you really sure you want to delete this server?'),
                'entityTitle' => $translator->trans('Server name: %name%', ['%name%' => $server->getName()])
            )
        );
    }

    /**
     * @Route("/{id}/check", name="server_check")
     */
    public function checkAction(EntityManagerInterface $entityManager, UpdateChecker $updateChecker, $id)
    {
        set_time_limit(300);

        /** @var Server $server */
        $server = $entityManager->getRepository(Server::class)->findOneBy(['id' => $id]);

        if (!$server) {
            return new JsonResponse([
                'success' => false
            ], 404);
        }

        $success = $updateChecker->run($server);

        if (!$success) {
            return new JsonResponse([
                'success' => false,
            ], 404);
        }

        return new JsonResponse([
            'updates' => $server->getUpdates(),
            'criticalUpdates' => $server->getCriticalUpdates()
        ]);
    }
}

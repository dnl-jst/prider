<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Entity\Server;
use AppBundle\Form\ServerType;
use AppBundle\Util\Ssh;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
            $this->addFlash(
                'success',
                $this->get('translator')->trans('Server "%name%" was created.', ['name' => $server->getName()])
            );

            $entityManager->persist($server);
            $entityManager->flush();

            return $this->redirectToRoute('server_index');
        }

        return $this->render('server/form.html.twig', [
            'title' => 'Server erstellen',
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
            $this->addFlash(
                'success',
                $this->get('translator')->trans('Server "%name%" was updated.', ['name' => $server->getName()])
            );

            $entityManager->flush();

            return $this->redirectToRoute('server_index');
        }

        return $this->render('server/form.html.twig', [
            'title' => 'Server bearbeiten',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name="server_delete")
     */
    public function deleteAction(Request $request, EntityManagerInterface $entityManager, $id)
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

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('Server "%name%" was deleted.', ['name' => $server->getName()])
                );
            }

            return $this->redirectToRoute('server_index');
        }

        return $this->render(
            'delete-form.html.twig',
            array(
                'headline' => $this->get('translator')->trans('Really delete server?'),
                'text' => $this->get('translator')->trans('Are you really sure you want to delete this server?'),
                'entityTitle' => $this->get('translator')->trans('Server name: %name%', ['name' => $server->getName()])
            )
        );
    }

    /**
     * @Route("/{id}/check", name="server_check")
     */
    public function checkAction(EntityManagerInterface $entityManager, Ssh $ssh, $id)
    {
        set_time_limit(300);

        /** @var Server $server */
        $server = $entityManager->getRepository(Server::class)->findOneBy(['id' => $id]);

        if (!$server) {
            return new JsonResponse([
                'success' => false
            ], 404);
        }

        switch ($server->getType()) {
            case 'apt':
                $command = 'sudo apt-get update && sudo apt-get -q -s upgrade';
                break;

            case 'yum':
                $command = 'sudo yum -C --security check-update';
                break;

            default:
                return new JsonResponse([
                    'success' => false
                ], 500);
        }

        if ($server->getKeyPair()) {

            $stream = $ssh->executeCommandWithKeyPair(
                $server->getHostname(),
                $server->getSshPort(),
                $server->getSshUser(),
                $server->getKeyPair()->getPrivateKey(),
                $server->getKeyPair()->getPublicKey(),
                $command
            );

        } else {

            $stream = $ssh->executeCommandWithPassword(
                $server->getHostname(),
                $server->getSshPort(),
                $server->getSshUser(),
                $server->getSshPassword(),
                $command
            );

        }

        $criticalUpdates = 0;

        do {
            $line = fgets($stream);

            switch ($server->getType()) {
                case 'apt':
                    if (preg_match('~(\d+) upgraded, (\d+) newly installed, (\d+) to remove and (\d+) not upgraded~', $line, $matches)) {
                        $server->setUpdates((int)$matches[1]);
                    }

                    if (preg_match('~^Inst ([^\s]+).*security.*\)$~', $line)) {
                        $criticalUpdates++;
                    }

                    break;

                case 'yum':
                    if (preg_match('~(No|\d+) packages needed for security; (\d+) packages available~', $line, $matches)) {

                        $server->setUpdates((int)$matches[2]);
                        $criticalUpdates = (int)$matches[1];

                    }

                    break;
            }
        } while (!feof($stream));

        $server->setCriticalUpdates($criticalUpdates);
        $server->setLastCheck(new \DateTime());

        $entityManager->flush();

        return new JsonResponse([
            'updates' => $server->getUpdates(),
            'criticalUpdates' => $server->getCriticalUpdates()
        ]);
    }

    /**
     * @Route("/{id}/upgrade", name="server_upgrade")
     */
    public function upgradeAction(EntityManagerInterface $entityManager, Ssh $ssh, $id)
    {
        set_time_limit(600);

        /** @var Server $server */
        $server = $entityManager->getRepository('AppBundle:Server')->findOneBy(['id' => $id]);

        if (!$server) {
            return new JsonResponse([
                'success' => false
            ], 404);
        }

        switch ($server->getType()) {
            case 'apt':
                $command = 'sudo apt-get update && sudo apt-get -y dist-upgrade';
                break;

            case 'yum':
                $command = 'sudo yum upgrade -y';
                break;

            default:
                return new JsonResponse([
                    'success' => false
                ], 500);
        }

        if ($server->getKeyPair()) {
            $stream = $ssh->executeCommandWithKeyPair(
                $server->getHostname(),
                $server->getSshPort(),
                $server->getSshUser(),
                $server->getKeyPair()->getPrivateKey(),
                $server->getKeyPair()->getPublicKey(),
                $command
            );
        } else {
            $stream = $ssh->executeCommandWithPassword(
                $server->getHostname(),
                $server->getSshPort(),
                $server->getSshUser(),
                $server->getSshPassword(),
                $command
            );
        }

        stream_set_blocking($stream, true);

        // wait for stream to finish
        stream_get_contents($stream);

        $server->setLastCheck(new \DateTime());
        $server->setLastUpgrade(new \DateTime());

        $entityManager->persist($server);
        $entityManager->flush();

        return $this->forward('AppBundle:Server:check', ['id' => $id]);
    }
}

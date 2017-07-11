<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Entity\Server;
use AppBundle\Util\Ssh;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/server")
 */
class ServerController extends Controller
{
    /**
     * @Route("/", name="server_index")
     */
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Group $groups */
        $groups = $em->createQuery('SELECT g, s FROM AppBundle:Group g JOIN g.servers s')->getResult();

        /** @var Server $ungroupedServers */
        $ungroupedServers = $em->createQuery('SELECT s FROM AppBundle:Server s WHERE s.group IS NULL')->getResult();

        return $this->render('server/index.html.twig', [
            'groups' => $groups,
            'ungroupedServers' => $ungroupedServers
        ]);
    }

    /**
     * @Route("/check/{id}", name="server_check")
     */
    public function checkAction($id, Ssh $ssh)
    {
        set_time_limit(0);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Server $server */
        $server = $em->getRepository('AppBundle:Server')->findOneBy(['id' => $id]);

        if (!$server) {

            return new JsonResponse([
                'success' => false
            ], 404);
        }

        $stream = $ssh->executeCommand(
            $server->getHostname(),
            $server->getSshPort(),
            $server->getSshUser(),
            $server->getSshPassword(),
            'sudo apt-get update && sudo apt-get -q -s upgrade'
        );

        $criticalUpdates = 0;

        do {

            $line = fgets($stream);

            if (preg_match('~(\d+) upgraded, (\d+) newly installed, (\d+) to remove and (\d+) not upgraded~', $line, $matches)) {
                $server->setUpdates($matches[1]);
            }

            if (preg_match('~^Inst ([^\s]+).*security.*\)$~', $line)) {
                $criticalUpdates++;
            }

        } while (!feof($stream));

        $server->setCriticalUpdates($criticalUpdates);
        $server->setLastCheck(new \DateTime());

        $em->persist($server);
        $em->flush();

        return new JsonResponse([
            'updates' => $server->getUpdates(),
            'criticalUpdates' => $server->getCriticalUpdates()
        ]);
    }

    /**
     * @Route("/upgrade/{id}", name="server_upgrade")
     */
    public function upgradeAction($id, Ssh $ssh)
    {
        set_time_limit(0);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Server $server */
        $server = $em->getRepository('AppBundle:Server')->findOneBy(['id' => $id]);

        if (!$server) {

            return new JsonResponse([
                'success' => false
            ], 404);
        }

        $stream = $ssh->executeCommand(
            $server->getHostname(),
            $server->getSshPort(),
            $server->getSshUser(),
            $server->getSshPassword(),
            'sudo apt-get update && sudo apt-get -y dist-upgrade'
        );

        stream_set_blocking($stream, true);

        // wait for stream to finish
        $response = stream_get_contents($stream);

        $server->setLastCheck(new \DateTime());
        $server->setLastUpgrade(new \DateTime());

        $em->persist($server);
        $em->flush();

        return $this->forward('AppBundle:Server:check', ['id' => $id]);
    }
}

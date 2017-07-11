<?php

namespace AppBundle\Controller;

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

        $response = new StreamedResponse();
        $response->setCallback(function () use ($stream, $server, $em) {

            $criticalUpdates = 0;

            do {

                $line = fgets($stream);
                echo $line;
                flush();
                ob_flush();

                if (preg_match('~(\d+) upgraded, (\d+) newly installed, (\d+) to remove and (\d+) not upgraded~', $line, $matches)) {
                    $server->setUpdates($matches[1]);
                }

                if (preg_match('~^Inst ([^\s]+).*security.*\)$~', $line)) {
                    $criticalUpdates++;
                }

            } while (!feof($stream));

            $server->setCritialUpdates($criticalUpdates);
            $server->setLastCheck(new \DateTime());

            $em->persist($server);
            $em->flush();

        });

        return $response;
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

        $response = new StreamedResponse();
        $response->setCallback(function () use ($stream, $server, $em) {

            do {

                $line = fgets($stream);
                echo $line;
                flush();
                ob_flush();

            } while (!feof($stream));

            $server->setLastCheck(new \DateTime());
            $server->setLastUpgrade(new \DateTime());

            $em->persist($server);
            $em->flush();

        });

        return $response;
    }
}

<?php

namespace AppBundle\Util;

use AppBundle\Entity\Server;
use Doctrine\ORM\EntityManagerInterface;

class UpdateChecker
{
    /** @var Ssh */
    private $ssh;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * UpdateCheckerService constructor.
     *
     * @param Ssh $ssh
     */
    public function __construct(Ssh $ssh, EntityManagerInterface $entityManager)
    {
        $this->ssh = $ssh;
        $this->entityManager = $entityManager;
    }

    public function run(Server $server)
    {
        switch ($server->getType()) {
            case 'apt':
                $command = 'sudo apt-get update 2>&1 && sudo apt-get -q -s upgrade 2>&1';
                break;

            case 'yum':
                $command = 'sudo yum -C --security check-update 2>&1';
                break;

            default:
                return false;
        }

        if ($server->getKeyPair()) {
            $stream = $this->ssh->executeCommandWithKeyPair(
                $server->getHostname(),
                $server->getSshPort(),
                $server->getSshUser(),
                $server->getKeyPair()->getPrivateKey(),
                $server->getKeyPair()->getPublicKey(),
                $command
            );
        } else {
            $stream = $this->ssh->executeCommandWithPassword(
                $server->getHostname(),
                $server->getSshPort(),
                $server->getSshUser(),
                $server->getSshPassword(),
                $command
            );
        }

        if ($stream === false) {
            return false;
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

        $this->entityManager->flush();

        return true;
    }
}
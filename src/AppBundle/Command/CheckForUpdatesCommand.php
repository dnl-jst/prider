<?php

namespace AppBundle\Command;

use AppBundle\Entity\Server;
use AppBundle\Util\UpdateChecker;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckForUpdatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:check-for-updates')
            ->setDescription('Check all servers for available updates.')
            ->setHelp('This command checks all servers for available updates.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        /** @var UpdateChecker $updateChecker */
        $updateChecker = $this->getContainer()->get(UpdateChecker::class);

        /** @var Server[] $servers */
        $servers = $em->getRepository(Server::class)->findAll();

        $output->writeln('checking '. count($servers) . ' servers for updates');

        $progress = new ProgressBar($output, count($servers));
        $progress->setFormatDefinition('custom', ' %current%/%max% [%bar%] %message%');
        $progress->setFormat('custom');
        $progress->setMessage('Starting to look for updates');
        $progress->start();

        foreach ($servers as $server) {
            $progress->setMessage($server->getName());
            $progress->advance();

            $updateChecker->run($server);
        }

        $progress->finish();
        $output->writeln('');
    }
}
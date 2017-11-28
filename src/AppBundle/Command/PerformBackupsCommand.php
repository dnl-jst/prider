<?php

namespace AppBundle\Command;

use AppBundle\Entity\BackupJob;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PerformBackupsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:perform-backups')
            ->setDescription('Perform all configured backup jobs.')
            ->setHelp('This command performs all configured backup jobs.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        /** @var BackupJob[] $backupJobs */
        $backupJobs = $em->getRepository(BackupJob::class)->findBy([]);

        $output->writeln('performing '. count($backupJobs) . ' backup jobs');

        $progress = new ProgressBar($output, count($backupJobs));
        $progress->setFormatDefinition('custom', ' %current%/%max% [%bar%] %message%');
        $progress->setFormat('custom');
        $progress->setMessage('Starting to perform backups');
        $progress->start();

        foreach ($backupJobs as $backupJob) {

            var_dump($backupJob->getParameterValueByKeyName('source-folders'));

            $progress->setMessage('[' . $backupJob->getBackupType()->getKeyName() . '] syncing ' . '' . ' from ' . $backupJob->getServer()->getName());
            $progress->advance();

            sleep(2);
        }

        $progress->finish();

        $output->writeln('');
    }
}
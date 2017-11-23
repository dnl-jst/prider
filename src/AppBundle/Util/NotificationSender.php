<?php

namespace AppBundle\Util;

use AppBundle\Entity\Server;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

class NotificationSender
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var \Swift_Mailer */
    private $swiftMailer;

    /** @var Environment */
    private $twig;

    public function __construct(EntityManagerInterface $entityManager, \Swift_Mailer $swiftMailer, Environment $twig)
    {
        $this->entityManager = $entityManager;
        $this->swiftMailer = $swiftMailer;
        $this->twig = $twig;
    }

    public function run(array $servers)
    {
        /** @var User[] $users */
        $users = $this->entityManager->getRepository(User::class)->findAll();

        $updatesAvailable = false;

        /** @var Server $server */
        foreach ($servers as $server) {
            if ($server->getUpdates() > 0) {
                $updatesAvailable = true;
            }
        }

        if ($updatesAvailable === false) {
            return;
        }

        foreach ($users as $user) {
            $mailBody = $this->twig->render(
                '_email/update-notification.txt.twig',
                [
                    'user' => $user,
                    'servers' => $servers,
                ]
            );

            $message = new \Swift_Message('Updates available');
            $message->setFrom('no-reply@prider');
            $message->setBody($mailBody);
            $message->setTo($user->getEmail());

            $this->swiftMailer->send($message);
        }
    }
}
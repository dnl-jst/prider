<?php

namespace AppBundle\Util;

class Ssh
{
    public function executeCommandWithPassword($hostname, $sshPort = 22, $sshUser, $sshPassword, $command)
    {
        $connection = ssh2_connect($hostname, $sshPort);

        if (!ssh2_auth_password($connection, $sshUser, $sshPassword)) {
            return false;
        }

        return ssh2_exec($connection, $command);
    }

    public function executeCommandWithKeyPair($hostname, $sshPort = 22, $sshUser, $sshPrivateKey, $sshPublicKey, $command)
    {
        $connection = ssh2_connect($hostname, $sshPort);

        $publicKeyFile = tempnam('/tmp', '__prider_');
        $privateKeyFile = tempnam('/tmp', '__prider_');

        file_put_contents($publicKeyFile, $sshPublicKey);
        file_put_contents($privateKeyFile, $sshPrivateKey);

        $success = ssh2_auth_pubkey_file($connection, $sshUser, $publicKeyFile, $privateKeyFile);

        unlink($publicKeyFile);
        unlink($privateKeyFile);

        if (!$success) {
            return false;
        }

        return ssh2_exec($connection, $command);
    }
}
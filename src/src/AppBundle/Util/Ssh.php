<?php

namespace AppBundle\Util;

class Ssh
{


    public function executeCommand($hostname, $sshPort = 22, $sshUser, $sshPassword, $command)
    {
        $connection = ssh2_connect($hostname, $sshPort);

        ssh2_auth_password($connection, $sshUser, $sshPassword);

        $stream = ssh2_exec($connection, $command);

        return $stream;
    }

}
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="backup_jobs")
 */
class BackupJob
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Server")
     * @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     */
    private $server;

    /**
     * @ORM\ManyToOne(targetEntity="BackupType")
     * @ORM\JoinColumn(name="backup_type_id", referencedColumnName="id")
     */
    private $backupType;

    /**
     * @var BackupJobParameter[]
     *
     * @ORM\OneToMany(targetEntity="BackupJobParameter", mappedBy="backupJob")
     */
    private $jobParameters;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param mixed $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    /**
     * @return mixed
     */
    public function getBackupType()
    {
        return $this->backupType;
    }

    /**
     * @param mixed $backupType
     */
    public function setBackupType($backupType)
    {
        $this->backupType = $backupType;
    }

    /**
     * @return BackupJobParameter[]
     */
    public function getJobParameters()
    {
        return $this->jobParameters;
    }

    /**
     * @param BackupJobParameter[] $jobParameters
     */
    public function setJobParameters($jobParameters)
    {
        $this->jobParameters = $jobParameters;
    }

    public function getParameterValueByKeyName($keyName)
    {
        $parameter = false;

        foreach ($this->getBackupType()->getParameters() as $backupParameter) {
            if ($backupParameter->getKeyName() === $keyName) {
                $parameter = $backupParameter;
                break;
            }
        }

        if (!$parameter) {
            return false;
        }

        foreach ($this->getJobParameters() as $jobParameter) {
            if ($jobParameter->getBackupParameter() === $parameter) {

                switch ($parameter->getType()) {
                    case 'array':
                        return explode(chr(10), $jobParameter->getValue());

                    default:
                        return $jobParameter->getValue();
                }


            }
        }

        return false;
    }
}

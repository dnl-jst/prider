<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="backup_job_parameters")
 */
class BackupJobParameter
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
     * @ORM\ManyToOne(targetEntity="BackupJob")
     * @ORM\JoinColumn(name="backup_job_id", referencedColumnName="id")
     */
    private $backupJob;

    /**
     * @ORM\ManyToOne(targetEntity="BackupParameter")
     * @ORM\JoinColumn(name="backup_parameter_id", referencedColumnName="id")
     */
    private $backupParameter;

    /**
     * @var string
     *
     * @ORM\Column(type="text", length=255)
     */
    private $value;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getBackupJob()
    {
        return $this->backupJob;
    }

    /**
     * @param mixed $backupJob
     */
    public function setBackupJob($backupJob)
    {
        $this->backupJob = $backupJob;
    }

    /**
     * @return mixed
     */
    public function getBackupParameter()
    {
        return $this->backupParameter;
    }

    /**
     * @param mixed $backupParameter
     */
    public function setBackupParameter($backupParameter)
    {
        $this->backupParameter = $backupParameter;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}

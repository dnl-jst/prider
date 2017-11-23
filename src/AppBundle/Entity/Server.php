<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="servers")
 */
class Server
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hostname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sshUser;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sshPassword;

    /**
     * @ORM\Column(type="integer")
     */
    private $sshPort = 22;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="Group")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $group;

    /**
     * @ORM\Column(type="integer")
     */
    private $updates = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $criticalUpdates = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastCheck;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastUpgrade;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type = 'apt';

    /**
     * @ORM\ManyToOne(targetEntity="KeyPair")
     * @ORM\JoinColumn(name="key_pair_id", referencedColumnName="id")
     */
    private $keyPair;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->created = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Server
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Server
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set hostname
     *
     * @param string $hostname
     *
     * @return Server
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Get hostname
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Set sshUser
     *
     * @param string $sshUser
     *
     * @return Server
     */
    public function setSshUser($sshUser)
    {
        $this->sshUser = $sshUser;

        return $this;
    }

    /**
     * Get sshUser
     *
     * @return string
     */
    public function getSshUser()
    {
        return $this->sshUser;
    }

    /**
     * Set sshPassword
     *
     * @param string $sshPassword
     *
     * @return Server
     */
    public function setSshPassword($sshPassword)
    {
        $this->sshPassword = $sshPassword;

        return $this;
    }

    /**
     * Get sshPassword
     *
     * @return string
     */
    public function getSshPassword()
    {
        return $this->sshPassword;
    }

    /**
     * Set sshPort
     *
     * @param integer $sshPort
     *
     * @return Server
     */
    public function setSshPort($sshPort)
    {
        $this->sshPort = $sshPort;

        return $this;
    }

    /**
     * Get sshPort
     *
     * @return integer
     */
    public function getSshPort()
    {
        return $this->sshPort;
    }

    /**
     * Set group
     *
     * @param \AppBundle\Entity\Group $group
     *
     * @return Server
     */
    public function setGroup(\AppBundle\Entity\Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \AppBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set updates
     *
     * @param integer $updates
     *
     * @return Server
     */
    public function setUpdates($updates)
    {
        $this->updates = $updates;

        return $this;
    }

    /**
     * Get updates
     *
     * @return integer
     */
    public function getUpdates()
    {
        return $this->updates;
    }

    /**
     * Set criticalUpdates
     *
     * @param integer $criticalUpdates
     *
     * @return Server
     */
    public function setCriticalUpdates($criticalUpdates)
    {
        $this->criticalUpdates = $criticalUpdates;

        return $this;
    }

    /**
     * Get criticalUpdates
     *
     * @return integer
     */
    public function getCriticalUpdates()
    {
        return $this->criticalUpdates;
    }

    /**
     * Set lastCheck
     *
     * @param \DateTime $lastCheck
     *
     * @return Server
     */
    public function setLastCheck($lastCheck)
    {
        $this->lastCheck = $lastCheck;

        return $this;
    }

    /**
     * Get lastCheck
     *
     * @return \DateTime
     */
    public function getLastCheck()
    {
        return $this->lastCheck;
    }

    /**
     * Set lastUpgrade
     *
     * @param \DateTime $lastUpgrade
     *
     * @return Server
     */
    public function setLastUpgrade($lastUpgrade)
    {
        $this->lastUpgrade = $lastUpgrade;

        return $this;
    }

    /**
     * Get lastUpgrade
     *
     * @return \DateTime
     */
    public function getLastUpgrade()
    {
        return $this->lastUpgrade;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Server
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set keyPair
     *
     * @param \AppBundle\Entity\KeyPair $keyPair
     *
     * @return Server
     */
    public function setKeyPair(\AppBundle\Entity\KeyPair $keyPair = null)
    {
        $this->keyPair = $keyPair;

        return $this;
    }

    /**
     * Get keyPair
     *
     * @return \AppBundle\Entity\KeyPair
     */
    public function getKeyPair()
    {
        return $this->keyPair;
    }
}

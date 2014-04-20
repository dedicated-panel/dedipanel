<?php

/*
** Copyright (C) 2010-2013 Kerouanton Albin, Smedts Jérôme
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along
** with this program; if not, write to the Free Software Foundation, Inc.,
** 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace DP\Core\MachineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DP\GameServer\GameServerBundle\Entity\GameServer;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\GroupInterface;
use Dedipanel\PHPSeclibWrapperBundle\Server\Server;
use Dedipanel\PHPSeclibWrapperBundle\Connection\ConnectionInterface;
use DP\Core\MachineBundle\Validator\CredentialsConstraint;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * DP\Core\MachineBundle\Entity\Machine
 *
 * @ORM\Table(name="machine")
 * @ORM\Entity(repositoryClass="DP\Core\MachineBundle\Entity\MachineRepository")
 */
class Machine extends Server
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var bigint $ip
     *
     * @ORM\Column(name="privateIp", type="string", length=15, nullable=true)
     */
    protected $ip;
    
    /**
     * @var bigint $publicIp
     *
     * @ORM\Column(name="publicIp", type="string", length=15, nullable=true)
     */
    protected $publicIp;
    
    /**
     * @var integer $port
     *
     * @ORM\Column(name="port", type="integer")
     */
    protected $port = 22;
    
    /**
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=16)
     */
    protected $username;
    
    /**
     * @var string $password
     */
    protected $password;
    
    /**
     * @var string $privateKeyName
     *
     * @ORM\Column(name="privateKeyName", type="string", length=23)
     */
    protected $privateKeyName;
    
    /**
     * @var string $home
     *
     * @ORM\Column(name="home", type="string", length=255, nullable=true)
     */
    protected $home;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $gameServers
     *
     * @ORM\OneToMany(targetEntity="DP\GameServer\GameServerBundle\Entity\GameServer", mappedBy="machine", cascade={"persist", "remove"})
     */
    protected $gameServers;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="nbCore", type="integer", nullable=true)
     */
    protected $nbCore;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="is64bit", type="boolean")
     */
    protected $is64bit = false;
    
    /**
     * @ORM\ManyToMany(targetEntity="DP\Core\UserBundle\Entity\Group")
     * @ORM\JoinTable(name="machine_to_groups",
     *      joinColumns={@ORM\JoinColumn(name="machine_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;
    
    /**
     * @var \Dedipanel\PHPSeclibWrapperBundle\Connection\ConnectionInterface $connection
     */
    protected $connection;
    
    
    public function __construct()
    {
        $this->gameServers = new ArrayCollection();
        $this->groups      = new ArrayCollection();
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
     * Set publicIp
     *
     * @param bigint $publicIp
     */
    public function setPublicIp($publicIp)
    {
        $this->publicIp = $publicIp;
    }

    /**
     * Get publicIp
     *
     * @return bigint
     */
    public function getPublicIp()
    {
        if (empty($this->publicIp)) {
            return $this->ip;
        }

        return $this->publicIp;
    }

    /**
     * Set publicKey
     *
     * @param string $publicKey
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * Get publicKey
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }
    
    public function addGameServer(GameServer $srv)
    {
        $srv->setMachine($this);
        $this->gameServers[] = $srv;
    }

    public function getGameServers()
    {
        return $this->gameServers;
    }

    /**
     * Set the number of core on the server
     *
     * @param integer $nbCore
     */
    public function setNbCore($nbCore)
    {
        $this->nbCore = $nbCore;
    }

    /**
     * Get the number of core on the server
     *
     * @return integer Number of core
     */
    public function getNbCore()
    {
        return $this->nbCore;
    }

    /**
     * Sets is 64 bit system
     *
     * @param integer $is64bit Is 64 bit system ?
     *
     * @return Machine
     */
    public function setIs64Bit($is64bit)
    {
        $this->is64bit = $is64bit;

        return $this;
    }

    /**
     * Gets is 64 bit system
     *
     * @return integer Is 64 bit system
     */
    public function getIs64Bit()
    {
        return $this->is64bit;
    }
    
    /**
     * Gets the groups granted to the user.
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    public function getGroupNames()
    {
        $names = array();
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
    }

    public function addGroup(GroupInterface $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    public function removeGroup(GroupInterface $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }
    
    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        
        return $this;
    }
    
    public function getConnection()
    {
        return $this->connection;
    }
    
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('ip', new Assert\Ip(array('message' => 'machine.assert.ip')));
        $metadata->addPropertyConstraint('publicIp', new Assert\Ip(array('message' => 'machine.assert.publicIp')));
        $metadata->addPropertyConstraint('port', new Assert\Range(array(
            'min' => 1, 
            'minMessage' => 'machine.assert.port', 
            'max' => 65536, 
            'maxMessage' => 'machine.assert.port', 
        )));
        $metadata->addPropertyConstraint('username', new Assert\NotBlank(array('message' => 'machine.assert.username')));
        $metadata->addConstraint(new Assert\Callback(array(
            'methods' => array('validateNotEmptyPassword'),
        )));
        $metadata->addConstraint(new CredentialsConstraint);
    }
    
    public function validateNotEmptyPassword(ExecutionContextInterface $context)
    {
        if (null === $this->getId() && null === $this->getPassword()) {
            $context->addViolation('machine.assert.password');
        }
    }
}

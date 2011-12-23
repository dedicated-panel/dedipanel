<?php

namespace DP\GameServer\GameServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\Core\MachineBundle\Entity\Machine;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DP\Core\GameServer\GameServerBundle\Entity\GameServer
 * @ORM\MappedSuperclass
 */
class GameServer
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=32)
     * @Assert\NotBlank(message="gameServer.assert.name")
     */
    protected $name;

    /**
     * @var integer $port
     *
     * @ORM\Column(name="port", type="integer")
     * @Assert\Min(limit="1", message="gameServer.assert.port")
     * @Assert\Max(limit="65536", message="gameServer.assert.port")
     */
    protected $port;

    /**
     * @var integer $installationStatus
     *
     * @ORM\Column(name="installationStatus", type="integer", nullable=true)
     */
    protected $installationStatus;

    /**
     * @var string $dir
     *
     * @ORM\Column(name="dir", type="string", length=64)
     * @Assert\NotBlank(message="gameServer.assert.dir")
     */
    protected $dir;

    /**
     * @var integer $maxplayers
     *
     * @ORM\Column(name="maxplayers", type="integer")
     * @Assert\Min(limit="2", message="gameServer.assert.maxplayers")
     */
    protected $maxplayers;
    
    /**
     * @ORM\ManyToOne(targetEntity="DP\Core\MachineBundle\Entity\Machine", inversedBy="gameServers")
     * @ORM\JoinColumn(name="machineId", referencedColumnName="id")
     * @Assert\NotNull(message="gameServer.assert.machine")
     */
    protected $machine;
    
    /**
     * @ORM\ManyToOne(targetEntity="DP\Core\GameBundle\Entity\Game", inversedBy="gameServers")
     * @ORM\JoinColumn(name="gameId", referencedColumnName="id")
     * * @Assert\NotNull(message="gameServer.assert.game")
     */
    protected $game;


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
     * Set machine
     *
     * @param Machine $machine
     */
    public function setMachine(Machine $machine)
    {
        $this->machine = $machine;
    }

    /**
     * Get machine
     *
     * @return Machine
     */
    public function getMachine()
    {
        return $this->machine;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * Set port
     *
     * @param integer $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Get port
     *
     * @return integer 
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set game
     *
     * @param Game $game
     */
    public function setGame($game)
    {
        $this->game = $game;
    }

    /**
     * Get gameId
     *
     * @return integer 
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * Set installationStatus
     *
     * @param integer $installationStatus
     */
    public function setInstallationStatus($installationStatus)
    {
        $this->installationStatus = $installationStatus;
    }

    /**
     * Get installationStatus
     *
     * @return integer 
     */
    public function getInstallationStatus()
    {
        return $this->installationStatus;
    }

    /**
     * Set dir
     *
     * @param string $dir
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Get dir
     *
     * @return string 
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Set maxplayers
     *
     * @param integer $maxplayers
     */
    public function setMaxplayers($maxplayers)
    {
        $this->maxplayers = $maxplayers;
    }

    /**
     * Get maxplayers
     *
     * @return integer 
     */
    public function getMaxplayers()
    {
        return $this->maxplayers;
    }
}
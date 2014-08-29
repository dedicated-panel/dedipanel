<?php

namespace DP\VoipServer\VoipServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\Core\MachineBundle\Entity\Machine;
use Symfony\Component\Validator\Constraints as Assert;
use DP\Core\CoreBundle\Model\AbstractServer;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * VoipServer
 *
 * @ORM\Table(name="voip_server")
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *      "teamspeak" = "DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServer"
 * })
 */
abstract class VoipServer extends AbstractServer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="DP\Core\MachineBundle\Entity\Machine", inversedBy="voipServers")
     * @ORM\JoinColumn(name="machineId", referencedColumnName="id")
     */
    protected $machine;

    /**
     * @var Doctrine\Common\Collections\ArrayCollection $instances
     *
     * @ORM\OneToMany(targetEntity="DP\VoipServer\VoipServerBundle\Entity\VoipServerInstance", mappedBy="server", cascade={"persist", "remove"})
     */
    protected $instances;

    /**
     * @var string $dir
     *
     * @ORM\Column(name="dir", type="string", length=64)
     * @Assert\NotBlank(message="voip.assert.dir.not_blank")
     */
    protected $dir;

    /**
     * @var object $query
     */
    protected $query;


    /**
     * Get the current type of server
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Get the port that need to be used by the query
     *
     * @return integer
     */
    abstract public function getQueryPort();

    public function __construct()
    {
        $this->instances = new ArrayCollection();
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
     * Set machine
     *
     * @param Machine $machine
     */
    public function setMachine(Machine $machine)
    {
        $this->machine = $machine;

        return $this;
    }

    public function addInstance(VoipServerInstance $instance)
    {
        $instance->setServer($this);
        $this->instances[] = $instance;
    }

    public function getInstances()
    {
        return $this->instances;
    }

    public function setDir($dir)
    {
        $this->dir = trim($dir, '/');

        return $this;
    }

    public function getDir()
    {
        return $this->dir;
    }

    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getHost()
    {
        return $this->getMachine()->getIP();
    }

    public function __toString()
    {
        return strval($this->machine);
    }
}

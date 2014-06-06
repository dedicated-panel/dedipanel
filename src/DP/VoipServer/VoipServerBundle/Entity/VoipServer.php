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
     * @var integer $installationStatus
     *
     * @ORM\Column(name="installationStatus", type="integer", nullable=true)
     */
    protected $installationStatus;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $instances
     *
     * @ORM\OneToMany(targetEntity="DP\VoipServer\VoipServerBundle\Entity\VoipServerInstance", mappedBy="server", cascade={"persist", "remove"})
     */
    protected $instances;


    /**
     * Get the current type of server
     *
     * @return string
     */
    abstract public function getType();

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

    /**
     * Get machine
     *
     * @return Machine
     */
    public function getMachine()
    {
        return $this->machine;
    }

    public function setInstallationStatus($status)
    {
        $this->installationStatus = $status;

        return $this;
    }

    public function getInstallationStatus()
    {
        return $this->installationStatus;
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

    public function __toString()
    {
        return strval($this->machine);
    }
}

<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\VoipServer\VoipServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\Core\CoreBundle\Exception\IPBannedException;
use DP\Core\CoreBundle\Model\AbstractServer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * VoipServerInstance
 *
 * @ORM\Table(name="voip_server_instance")
 * @ORM\Entity(repositoryClass="DP\VoipServer\VoipServerBundle\Entity\VoipServerInstanceRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *      "teamspeak" = "DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServerInstance"
 * })
 */
abstract class VoipServerInstance extends AbstractServer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32)
     * @Assert\NotBlank(message="voip.instance.assert.name.not_blank")
     * @Assert\Length(max="32", maxMessage="voip.instance.assert.name.max_len")
     */
    protected $name;

    /**
     * @var integer $port
     *
     * @ORM\Column(name="port", type="integer")
     * @Assert\Range(
     *      min = 1024, minMessage = "gameServer.assert.port",
     *      max = 65536, maxMessage = "gameServer.assert.port"
     * )
     * @Assert\NotBlank(message="gameServer.assert.port")
     */
    protected $port = 9987;

    /**
     * @ORM\ManyToOne(targetEntity="DP\VoipServer\VoipServerBundle\Entity\VoipServer", inversedBy="instances")
     * @ORM\JoinColumn(name="serverId", referencedColumnName="id")
     */
    protected $server;

    /**
     * @ORM\Column(name="max_clients", type="integer")
     * @Assert\NotBlank(message="voip.instance.assert.max_clients.not_blank")
     * @Assert\GreaterThan(value="0", message="voip.instance.assert.max_clients.not_zero")
     *
     * @var integer $maxClients
     */
    protected $maxClients;


    /**
     * Get the current type of server
     *
     * @return string
     */
    abstract public function getType();

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
     * Set the instance name
     *
     * @param string $name
     * @return VoipServerInstance
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the instance name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the instance port
     *
     * @param integer $port
     * @return VoipServerInstance
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get the instance port
     *
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set the server on which the instance is present
     *
     * @param VoipServer $server
     * @return VoipServerInstance
     * @throws \InvalidArgumentException Throws if the server and the instance type does not match
     */
    public function setServer(VoipServer $server)
    {
        if ($server->getType() != $this->getType()) {
            throw new \InvalidArgumentException('You need to provide the same type of VoipServer that the current VoipServerInstance is.');
        }

        $this->server = $server;
        $this->dir    = $server->getDir();

        $server->addInstance($this);

        return $this;
    }

    /**
     * Get the server on which the instance is present
     *
     * @return VoipServer
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Set the maximum clients for this instance
     *
     * @param integer $maxClients
     * @return VoipServerInstance
     */
    public function setMaxClients($maxClients)
    {
        $this->maxClients = $maxClients;

        return $this;
    }

    /**
     * Get the maximum clients for this instance
     *
     * @return integer
     */
    public function getMaxClients()
    {
        return $this->maxClients;
    }

    public function setQuery($query)
    {
        $this->server->setQuery($query);

        return $this;
    }

    public function getQuery()
    {
        return $this->server->getQuery();
    }

    /**
     * {@inheritdoc}
     */
    public function getMachine()
    {
        return $this->getServer()->getMachine();
    }

    // Do nothing
    public function deleteInstallDir()
    {
        return true;
    }

    public function validateServer(ExecutionContextInterface $context)
    {
        try {
            if ($this->getServer() !== null && (!$this->getQuery()->isOnline()
                || (!$this->getQuery()->isConnected() && !$this->getQuery()->login()))) {
                $context->buildViolation('voip.instance.assert.offline_server')->addViolation();
            }
        } catch (IPBannedException $e) {
            $context->buildViolation('voip.instance.assert.banned_from_server')
                ->setParameter('%duration%', $e->getDuration())
                ->addViolation();
        }
    }
}

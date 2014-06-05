<?php

namespace DP\VoipServer\VoipServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
abstract class VoipServerInstance
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
     * @Assert\NotBlank(message="voip.assert.name.not_blank")
     * @Assert\Length(max="32", maxMessage="voip.assert.name.max_len")
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="DP\VoipServer\VoipServerBundle\Entity\VoipServer", inversedBy="instances")
     * @ORM\JoinColumn(name="serverId", referencedColumnName="id")
     */
    protected $server;


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

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setServer(VoipServer $server)
    {
        if ($server->getType() != $this->getType()) {
            throw new \InvalidArgumentException('You need to provide the same type of VoipServer that the current VoipServerInstance is.');
        }

        $this->server = $server;

        return $this;
    }

    public function getServer()
    {
        return $this->server;
    }
}

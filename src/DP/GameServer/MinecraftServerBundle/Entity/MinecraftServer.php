<?php

namespace DP\GameServer\MinecraftServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\GameServer\GameServerBundle\Entity\GameServer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DP\GameServer\MinecraftServerBundle\Entity\MinecraftServer
 *
 * @ORM\Table(name="minecraft_server")
 * @ORM\Entity(repositoryClass="DP\GameServer\MinecraftServerBundle\Entity\MinecraftServerRepository")
 */
class MinecraftServer extends GameServer
{
    /**
     * @var integer $queryPort
     *
     * @ORM\Column(name="queryPort", type="integer")
     * @Assert\Min(limit="1", message="gameServer.assert.queryPort.min")
     * @Assert\Max(limit="65536", message="gameServer.assert.queryPort.max")
     */
    protected $queryPort;
    
    public function setQueryPort($queryPort)
    {
        $this->queryPort = $queryPort;
    }
    
    public function getQueryPort()
    {
        return $this->queryPort;
    }
}

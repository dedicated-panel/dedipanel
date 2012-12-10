<?php

namespace DP\GameServer\MinecraftServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\GameServer\GameServerBundle\Entity\GameServer;

/**
 * DP\GameServer\MinecraftServerBundle\Entity\MinecraftServer
 *
 * @ORM\Table(name="minecraft_server")
 * @ORM\Entity(repositoryClass="DP\GameServer\MinecraftServerBundle\Entity\MinecraftServerRepository")
 */
class MinecraftServer extends GameServer
{
}

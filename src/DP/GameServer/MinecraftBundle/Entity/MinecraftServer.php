<?php

namespace DP\GameServer\MinecraftBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\GameServer\GameServerBundle\Entity\GameServer;

/**
 * DP\GameServer\MinecraftBundle\Entity\MinecraftServer
 *
 * @ORM\Table(name="minecraft_server")
 * @ORM\Entity(repositoryClass="DP\GameServer\MinecraftBundle\Entity\MinecraftServerRepository")
 */
class MinecraftServer extends GameServer
{
}

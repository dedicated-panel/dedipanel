<?php

namespace DP\VoipServer\TeamspeakServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\VoipServer\VoipServerBundle\Entity\VoipServer;

/**
 * TeamspeakServer
 *
 * @ORM\Table(name="teamspeak_server")
 * @ORM\Entity(repositoryClass="DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServerRepository")
 */
class TeamspeakServer extends VoipServer
{
}

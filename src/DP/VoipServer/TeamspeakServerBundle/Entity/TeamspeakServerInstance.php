<?php

namespace DP\VoipServer\TeamspeakServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\VoipServer\VoipServerBundle\Entity\VoipServerInstance;

/**
 * TeamspeakServerInstance
 *
 * @ORM\Table(name="teamspeak_server_instance")
 * @ORM\Entity(repositoryClass="DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServerInstanceRepository")
 */
class TeamspeakServerInstance extends VoipServerInstance
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return 'teamspeak';
    }
}

<?php

namespace DP\VoipServer\TeamspeakServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\VoipServer\VoipServerBundle\Entity\VoipServerInstance;

/**
 * TeamspeakServerInstance
 *
 * @ORM\Table()
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
    private $id;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}

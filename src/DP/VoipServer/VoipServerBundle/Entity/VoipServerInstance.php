<?php

namespace DP\VoipServer\VoipServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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

<?php

namespace DP\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * DP\Core\UserBundle\Entity\User
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class User extends BaseUser {
    /**
     * @var integer $id
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
	
    public function __construct()
    {
        parent::__construct();
    }
}
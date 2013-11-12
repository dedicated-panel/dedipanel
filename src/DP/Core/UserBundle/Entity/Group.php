<?php

namespace DP\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\Group as BaseGroup;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Group
 * 
 * @ORM\Table(name="fos_user_group")
 * @ORM\Entity
 * @UniqueEntity(fields="name", message="group_admin.name.unique")
 */
class Group extends BaseGroup
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
    
    public function __toString()
    {
        return $this->name;
    }
}

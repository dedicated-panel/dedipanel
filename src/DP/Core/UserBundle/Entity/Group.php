<?php

namespace DP\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\Group as BaseGroup;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Group
 * 
 * @ORM\Table(name="fos_user_group")
 * @ORM\Entity(repositoryClass="DP\Core\UserBundle\Entity\GroupRepository")
 * @UniqueEntity(fields="name", message="group_admin.assert.name.unique")
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
     * @var string
     * 
     * @Assert\NotBlank(message="group_admin.assert.name.empty")
     */
    protected $name;
    
    
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

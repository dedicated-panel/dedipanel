<?php

namespace DP\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\Group as BaseGroup;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Tree\Traits\NestedSetEntity;

/**
 * Group
 *
 * Table need to be suffixed for avoiding SQL keyword conflict
 * @ORM\Table(name="group_table")
 * @ORM\Entity(repositoryClass="DP\Core\UserBundle\Entity\GroupRepository")
 * @UniqueEntity(fields="name", message="group_admin.assert.name.unique")
 * @Gedmo\Tree(type="nested")
 */
class Group extends BaseGroup
{
    use NestedSetEntity;
    
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
     * @var Group
     * 
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="DP\Core\UserBundle\Entity\Group", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;
    
    /**
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="DP\Core\UserBundle\Entity\Group", mappedBy="parent")
     */
    protected $children;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="DP\Core\UserBundle\Entity\User", mappedBy="groups")
     */
    protected $users;

    protected $roles = array();
    
    
    public function __construct($name = '', $roles = array())
    {
        parent::__construct($name, $roles);
        
        $this->children = new ArrayCollection();
        $this->users    = new ArrayCollection();
    }
    
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
    
    public function setParent(Group $parent = null)
    {
        $this->parent = $parent;
        
        return $this;
    }
    
    public function getParent()
    {
        return $this->parent;
    }
    
    public function setChildren(array $children = array())
    {
        if (is_null($children)) {
            $children = array();
        }
        
        $this->children = new ArrayCollection($children);
        
        return $this;
    }
    
    public function addChildren(Group $child)
    {
        $this->children[] = $child;
        
        return $this;
    }
    
    public function removeChildren(Group $child)
    {
        $this->children->removeElement($child);
        
        return $this;
    }
    
    public function getChildren()
    {
        return $this->children;
    }

    public function setUsers(array $users = array())
    {
        $this->users = new ArrayCollection($users);

        return $this;
    }

    public function getUsers()
    {
        return $this->users;
    }
}

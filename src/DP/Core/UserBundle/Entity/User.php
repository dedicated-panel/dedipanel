<?php

namespace DP\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="fos_user_user")
 * @ORM\Entity(repositoryClass="DP\Core\UserBundle\Entity\UserRepository")
 * @UniqueEntity(fields="username", message="user_admin.assert.username.unique")
 * @UniqueEntity(fields="email", message="user_admin.assert.email.unique")
 */
class User extends BaseUser
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
     * @Assert\NotBlank(message="user_admin.assert.username.empty")
     */
    protected $username;

    /**
     * @var string
     * 
     * @Assert\NotBlank(message="user_admin.assert.email.empty")
     * @Assert\Email(message="user_admin.assert.email.valid")
     */
    protected $email;
    
    /**
     * @Assert\NotNull(message="user_admin.assert.password.empty")
     */
    protected $password;
    
    /**
     * @var \DateTime
     * 
     * @ORM\Column(name="createdAt", type="datetime", nullable=true)
     */
    protected $createdAt;
    
    /**
     * @ORM\ManyToMany(targetEntity="DP\Core\UserBundle\Entity\Group")
     * @ORM\JoinTable(name="fos_user_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;


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
        return $this->username;
    }
    
    public function setCreatedAt(\DateTime $date)
    {
        $this->createdAt = $date;
        
        return $this;
    }
    
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}

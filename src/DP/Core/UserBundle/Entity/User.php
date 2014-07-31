<?php

namespace DP\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * User
 *
 * Table need to be suffixed for avoiding SQL keyword conflict
 * @ORM\Table(name="user_table")
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

    /**
     * @param bool $admin
     */
    public function setAdmin($admin)
    {
        if ($admin == true && !$this->isAdmin()) {
            $this->addRole('ROLE_ADMIN');
        }
        elseif ($admin == false && $this->isAdmin()) {
            $this->removeRole('ROLE_ADMIN');
        }
    }

    /**
     * Is current user an admin ?
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('ROLE_ADMIN');
    }

    /**
     * @param bool $userAdmin
     */
    public function setUserAdmin($userAdmin)
    {
        if ($userAdmin == true && !$this->isSuperAdmin()) {
            $this->addRole('ROLE_SUPER_ADMIN');
        }
        elseif ($userAdmin == false && $this->isSuperAdmin()) {
            $this->removeRole('ROLE_SUPER_ADMIN');
        }
    }

    /**
     * Is current user a super admin ?
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('ROLE_SUPER_ADMIN');
    }

    /**
     * @Assert\Callback
     */
    public function validateRoleGroups(ExecutionContextInterface $context)
    {
        if ($this->isSuperAdmin() && !$this->getGroups()->isEmpty()) {
            $context->addViolationAt(
                'groups',
                'user_admin.assert.groups.super_admin'
            );
        }
        elseif (!$this->isSuperAdmin() && $this->getGroups()->isEmpty()) {
            $context->addViolationAt(
                'groups',
                'user_admin.assert.groups.empty'
            );
        }
    }
}

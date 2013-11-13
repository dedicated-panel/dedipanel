<?php

namespace DP\Admin\AdminBundle\Security;

use DP\Core\UserBundle\Security\RoleBuilderInterface;

class AdminRoleBuilder implements RoleBuilderInterface
{
    private $children;
    
    public function __construct(array $children = array())
    {
        $this->children = $children;
    }
    
    public function getBaseRole()
    {
        return 'ROLE_DP_ADMIN';
    }
    
    public function getRoleHierarchy()
    {
        $roles = array();
        
        foreach ($this->children AS $children) {
            if ($children instanceof ChildRoleBuilder) {
                $roles[] = $children->getRole();
            }
        }
        
        return $roles;
    }
}

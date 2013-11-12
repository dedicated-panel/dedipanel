<?php

namespace DP\GameServer\GameServerBundle\Security;

use DP\Core\UserBundle\Security\RoleBuilderInterface;

class GameRoleBuilder implements RoleBuilderInterface
{
    private $children = array();
    
    public function __construct($children = array())
    {
        if (!empty($children)) {
            $this->children = $children;
        }
    }
    
    public function getBaseRole()
    {
        return 'ROLE_DP_GAME';
    }
    
    public function getRoleHierarchy()
    {
        $hierarchy = array();
        $adminRoles = array();
        
        foreach ($this->children AS $roleBuilder) {
            $baseRole = $roleBuilder->getBaseRole();
            $roles = $roleBuilder->getRoles();

            $hierarchy += $roles;
            $adminRoles[] = $this->getRoleName('ADMIN', $baseRole);
        }
        
        if (!empty($adminRoles)) {
            $role = $this->getRoleName('ADMIN');
            $hierarchy[$role] = $adminRoles;
        }
        
        return $hierarchy;
    }
    
    private function getRoleName($roleSuffix, $rolePrefix = null)
    {
        if (empty($rolePrefix)) {
            $rolePrefix = $this->getBaseRole();
        }
        
        return sprintf('%s_%s', $rolePrefix, $roleSuffix);
    }
}

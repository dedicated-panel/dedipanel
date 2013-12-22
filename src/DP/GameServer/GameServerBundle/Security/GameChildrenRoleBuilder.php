<?php

namespace DP\GameServer\GameServerBundle\Security;

/**
 * RoleBuilder utilisé par les jeux afin de construire la liste des droits
 */
abstract class GameChildrenRoleBuilder
{
    abstract public function getBaseRole();
    
    protected function getRoleHierarchy()
    {
        return array(
            // 'SHOW'   => array('SHOW'), 
            'ADD'    => array('SHOW', 'ADD'), 
            'EDIT'   => array('SHOW', 'EDIT'), 
            'DELETE' => array('SHOW', 'DELETE'), 
            'STATE'  => array('SHOW', 'STATE'), 
            'FTP'    => array('SHOW', 'FTP'), 
            'PLUGIN' => array('SHOW', 'PLUGIN'), 
            'RCON'   => array('SHOW', 'RCON'),  
        );
    }
    
    protected function getRoleSuffixes()
    {
        return array_map(function ($str) { return '%s_' . $str; }, array_keys($this->getRoleHierarchy()));
    }
    
    protected function getRoleName($roleSuffix)
    {
        return sprintf('%s_%s', $this->getBaseRole(), $roleSuffix);
    }
    
    public function getRoles()
    {
        $baseRole = $this->getBaseRole();
        $hierarchy = $this->getRoleHierarchy();
        $roles = array();
        
        foreach ($hierarchy AS $role => $subroles) {            
            if (is_string($role) && is_array($subroles)) {
                $role = $this->getRoleName($role);
                
                $roles[$role] = array_map(array($this, 'getRoleName'), $subroles);
            }
            else {
                $role = $this->getRoleName($subroles);
                
                $roles[] = $role;
            }
        }
        
        if (!empty($roles)) {
            $adminRole = $this->getRoleName('ADMIN');
            $roles[$adminRole] = array_keys($roles);
        }
        
        return $roles;
    }
}

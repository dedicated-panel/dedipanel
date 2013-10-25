<?php

namespace Application\Sonata\UserBundle\Security;

use Sonata\UserBundle\Security\EditableRolesBuilder as BaseBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EditableRolesBuilder extends BaseBuilder
{
    /**
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {        
        $this->container = $container;
    }
    
    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = array();
        $rolesReadOnly = array();

        if (!$this->securityContext->getToken()) {
            return array($roles, $rolesReadOnly);
        }
        
        list($adminRoles, $adminRolesReadOnly) = $this->getRolesfromAdminClasses();
        list($dpRoles, $dpRolesReadOnly) = $this->getRolesFromDPRoleClasses();
        $containerRoles = $this->getRolesFromServiceContainer();
        
        $roles = array_merge($roles, $adminRoles, $dpRoles, $containerRoles);
        $rolesReadOnly = array_merge($rolesReadOnly, $adminRolesReadOnly, $dpRolesReadOnly);
        
        return array($roles, $rolesReadOnly);
    }
    
    /**
     * @return array
     */
    private function getRolesFromAdminClasses()
    {
        $roles = array();
        $rolesReadOnly = array();
        
        // get roles from the Admin classes
        foreach ($this->pool->getAdminServiceIds() as $id) {
            try {
                $admin = $this->pool->getInstance($id);
            } catch (\Exception $e) {
                continue;
            }

            $isMaster = $admin->isGranted('MASTER');
            $securityHandler = $admin->getSecurityHandler();
            // TODO get the base role from the admin or security handler
            $baseRole = $securityHandler->getBaseRole($admin);

            foreach ($admin->getSecurityInformation() as $role => $permissions) {
                $role = sprintf($baseRole, $role);

                if ($isMaster) {
                    // if the user has the MASTER permission, allow to grant access the admin roles to other users
                    $roles[$role] = $role;
                } elseif ($this->securityContext->isGranted($role)) {
                    // although the user has no MASTER permission, allow the currently logged in user to view the role
                    $rolesReadOnly[$role] = $role;
                }
            }
        }

        return array($roles, $rolesReadOnly);
    }

    /**
     * @return array
     */
    private function getRolesFromServiceContainer()
    {        
        $isMaster = $this->securityContext->isGranted('ROLE_SUPER_ADMIN');
        $roles = array();

        // get roles from the service container
        foreach ($this->rolesHierarchy as $name => $rolesHierarchy) {
            if ($this->securityContext->isGranted($name) || $isMaster) {
                $roles[$name] = $name . ': ' . implode(', ', $rolesHierarchy);

                foreach ($rolesHierarchy as $role) {
                    if (!isset($roles[$role])) {
                        $roles[$role] = $role;
                    }
                }
            }
        }
        
        return $roles;
    }
    
    /**
     * @return array
     */
    private function getRolesFromDPRoleClasses()
    {
        $roles = array();
        $rolesReadOnly = array();
        
        return array($roles, $rolesReadOnly);
    }
}

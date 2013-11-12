<?php

namespace DP\Core\UserBundle\Form\Modifier;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\Role;

class SecurityRolesModifier implements EventSubscriberInterface
{
    private $roleHierarchy;
    private $roles;
    
    public function __construct(RoleHierarchyInterface $roleHierarchy, array $roles)
    {
        $this->roleHierarchy = $roleHierarchy;
        $this->roles = array_keys($roles);
    }
    
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'completeRolesByDepth', 
        );
    }
    
    public function completeRolesByDepth(FormEvent $event)
    {
        $form = $event->getForm();
        $entity = $form->getParent()->getData();
        
        $selectedRoles = array();
        $hierarchy = $this->getHierarchy();
        
        foreach ($this->roles AS $role) {            
            if ($entity->hasRole($role)) {
                $selectedRoles[] = $role;
                $selectedRoles = array_merge($selectedRoles, $hierarchy[$role]);
            }
        }
        
        $event->setData(array_unique($selectedRoles));
    }
    
    public function getHierarchy()
    {
        $hierarchy = array();
        
        foreach ($this->roles AS $role) {
            $hierarchy[$role] = array();
            
            foreach ($this->roleHierarchy->getReachableRoles(array(new Role($role))) AS $childRole) {
                $hierarchy[$role][] = $childRole->getRole();
            }
        }
        
        return $hierarchy;
    }
}

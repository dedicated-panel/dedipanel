<?php

namespace DP\Core\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use DP\Core\UserBundle\Security\RoleBuilderInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Ajoute les rôles du panel au container
 * Récupère les rôles via les services taggués 'dp.role_builder'
 */
class AddDedipanelRolesCompilerPass implements CompilerPassInterface
{
    /**
     * Injecte les rôles du panel dans le container
     */
    public function process(ContainerBuilder $container)
    {        
        $roles = $this->getDedipanelRoles($container);
        
        $container->setParameter('security.role_hierarchy.roles', $roles);
    }
    
    /**
     * Récupère tous les services taggués 'dp.role_builder', 
     * y injecte les RoleBuilder enfant taggués selon le nom défini 
     * au niveau des tags parents (= 'dp.role_builder')
     * Puis exécute les 
     */
    private function getDedipanelRoles(ContainerBuilder $container)
    {
        $roles = $container->getParameter('security.role_hierarchy.roles');
        $services = $container->findTaggedServiceIds('dp.role_builder');
        $adminRoles = array();
        
        foreach ($services AS $id => $attrs) {
            $service = $container->get($id);
            
            // Vérifie que le service récupéré est bien un RoleBuilder du panel
            // et que le nom du tag des RoleBuilder enfant est fourni
            if ($service instanceof RoleBuilderInterface) {                
                $adminRole = $service->getBaseRole() . '_ADMIN';
                $hierarchy = $service->getRoleHierarchy();
                
                if (!empty($hierarchy)) {
                    $adminRoles[] = $adminRole;
                    $roles += $hierarchy;
                }
            }
        }
        
        $roles['ROLE_ADMIN'] = array_merge($roles['ROLE_ADMIN'], $adminRoles);
        
        return $roles;
    }
}

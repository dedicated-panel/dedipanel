<?php

namespace DP\Core\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use DP\Core\UserBundle\Security\RoleBuilderInterface;

class RemoveTopRoleBuilderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $services = array_keys($container->findTaggedServiceIds('dp.role_builder'));
        
        foreach ($services AS $id) {
            $service = $container->get($id);
            
            if ($service instanceof RoleBuilderInterface) {
                $container->removeDefinition($id);
            }
        }
    }
}

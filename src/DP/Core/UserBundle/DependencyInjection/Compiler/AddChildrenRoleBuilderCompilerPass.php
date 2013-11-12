<?php

namespace DP\Core\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use DP\Core\UserBundle\Security\RoleBuilderInterface;
use Symfony\Component\DependencyInjection\Reference;

class AddChildrenRoleBuilderCompilerPass implements CompilerPassinterface
{
    public function process(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds('dp.role_builder');
        
        foreach ($services AS $id => $attrs) {
            $class = new \ReflectionClass($container->getDefinition($id)->getClass());
            
            if (in_array('DP\Core\UserBundle\Security\RoleBuilderInterface', $class->getInterfaceNames()) 
            && $attrs[0]['children_builder_tag']) {
                $tag = $attrs[0]['children_builder_tag'];
                
                $definition = $container->getDefinition($id);
                $children = array_keys($container->findTaggedServiceIds($tag));
                $params = array();
                
                foreach ($children AS $child) {
                    $params[] = new Reference($child);
                }
                
                $definition->setArguments(array($params));
                
                $container->removeDefinition($id);
                $container->setDefinition($id, $definition);
                
                $service = $container->get($id);
            }
        }
    }
}

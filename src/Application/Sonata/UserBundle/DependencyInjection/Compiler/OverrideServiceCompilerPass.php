<?php

namespace Application\Sonata\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {        
        $service       = $container->getDefinition('sonata.user.editable_role_builder');
        $classOverride = $container->getParameterBag()->get('sonata.user.editable_role_builder.class');
        
        if ($service && $classOverride) {
            $service->setClass($classOverride);
            
            if (!$service->hasMethodCall('setContainer')) {
                $service->addMethodCall('setContainer', array(new Reference('service_container')));
            }
        }
    }
}

<?php

namespace DP\Core\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UpdateWatcherCompilerPass implements CompilerPassInterface
{
    /**
     * Désactive l'UpdateWatcher si l'environnement actuel n'est pas celui de prod.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->getParameterBag()->get('kernel.environment') != 'prod') {
            $container->removeDefinition('dp_core.update_watcher.service');
            $container->removeDefinition('dp_core.update_watcher.extension');

            // Le service étant taggué comme une extension twig,
            // on doit supprimer l'appel à addExtension sur ce service
            $calls = array_filter($container->getDefinition('twig')->getMethodCalls(), function ($call) {
                return strval($call[1][0]) != 'dp_core.update_watcher.extension';
            });
            $container->getDefinition('twig')->setMethodCalls($calls);
        }
    }
}

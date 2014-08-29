<?php

namespace DP\Core\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use DP\Core\CoreBundle\DependencyInjection\Compiler\PhpseclibDebugCompilerPass;
use DP\Core\CoreBundle\DependencyInjection\Compiler\UpdateWatcherCompilerPass;

class DPCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PhpseclibDebugCompilerPass);
        $container->addCompilerPass(new UpdateWatcherCompilerPass);
    }
}

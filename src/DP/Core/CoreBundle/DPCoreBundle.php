<?php

namespace DP\Core\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use DP\Core\CoreBundle\DependencyInjection\Compiler\PhpseclibDebugCompilerPass;

class DPCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PhpseclibDebugCompilerPass);
    }
}

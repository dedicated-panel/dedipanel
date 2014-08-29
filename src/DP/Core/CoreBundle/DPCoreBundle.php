<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

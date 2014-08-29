<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Modify services requiring logger with phpseclib channel
 * and inject NullLogger if debug is disabled
 *
 * @package DP\Core\CoreBundle\DependencyInjection\Compiler
 */
class PhpseclibDebugCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameterBag()->get('dp_core.debug')) {
            $this->replaceByNullLogger($container, 'monolog.handler.phpseclib_wrapper');
            $this->replaceByNullLogger($container, 'monolog.handler.phpseclib_internal');
        }
    }

    private function replaceByNullLogger(ContainerBuilder $container, $handler)
    {
        $def = $container->getDefinition($handler);
        $def->setClass('%monolog.handler.null.class%');

        $container->setDefinition($handler, $def);
    }
}

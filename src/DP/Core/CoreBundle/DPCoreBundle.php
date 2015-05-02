<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use DP\Core\CoreBundle\DependencyInjection\Compiler\PhpseclibDebugCompilerPass;
use Symfony\Component\Config\Resource\FileResource;

class DPCoreBundle extends Bundle
{
}

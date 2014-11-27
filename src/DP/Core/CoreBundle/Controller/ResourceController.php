<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController as BaseResourceController;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResourceController extends BaseResourceController
{
    /**
     * @var FlashHelper
     */
    protected $flashHelper;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        if ($container !== null) {
            $this->flashHelper = new FlashHelper(
                $this->config,
                $container->get('translator'),
                $container->get('session')
            );
        }
    }
}

<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\MachineBundle\Controller;

use DP\Core\CoreBundle\Controller\ResourceController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use DP\Core\MachineBundle\Entity\Machine;

/**
 * Machine controller.
 */
class MachineController extends ResourceController
{
    /**
     * @var DomainManager
     */
    protected $domainManager;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        if ($container !== null) {
            $this->domainManager = new DomainManager(
                $container->get($this->config->getServiceName('manager')),
                $container->get('event_dispatcher'),
                $this->flashHelper,
                $this->config
            );
        }
    }

    public function testConnectionAction(Request $request)
    {
        $this->isGrantedOr403('SHOW', $this->find($request));

        $config = $this->getConfiguration();
        /** @var Machine $machine */
        $machine = $this->findOr404($request);

        $test = $this->domainManager->connectionTest($machine);
        
        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('connection_test.html'))
            ->setData(array(
                $config->getResourceName() => $machine,
                'test' => $test,
            ))
        ;

        return $this->handleView($view);
    }
}

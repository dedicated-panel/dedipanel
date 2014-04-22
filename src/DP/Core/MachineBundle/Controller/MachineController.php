<?php

/*
** Copyright (C) 2010-2013 Kerouanton Albin, Smedts Jérôme
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along
** with this program; if not, write to the Free Software Foundation, Inc.,
** 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
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

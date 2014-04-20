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

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use DP\Core\MachineBundle\Entity\Machine;
use Symfony\Component\HttpFoundation\Request;

/**
 * Machine controller.
 */
class MachineController extends ResourceController
{
    public function testConnectionAction(Request $request)
    {
        $this->isGrantedOr403('SHOW', $this->find($request));

        $config = $this->getConfiguration();
        /** @var Machine $machine */
        $machine = $this->findOr404($request);

        $test = false;
        $compatLib = false;
        $javaInstalled = false;
        $screenInstalled = false;
        
        $test = $machine->getConnection()->testSSHConnection();
        
        if ($test == true) {
            $conn = $machine->getConnection();
            
            $machine->setHome($conn->getHome());
            $machine->setNbCore($conn->retrieveNbCore());
            $machine->setIs64bit($conn->is64bitSystem());
    
            if ($machine->getIs64Bit()) {
                $compatLib = $conn->hasCompatLib();
            }
    
            $javaInstalled   = $conn->isJavaInstalled();
            $screenInstalled = $conn->isInstalled('screen');

            $this->domainManager->update($machine);
        }
        
        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('connection_test.html'))
            ->setData(array(
                $config->getResourceName() => $machine,
                'result' => $test,
                'hasCompatLib' => $compatLib,
                'javaInstalled' => $javaInstalled,
                'screenInstalled' => $screenInstalled,
            ))
        ;

        return $this->handleView($view);
    }
}

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
use DP\Core\MachineBundle\PHPSeclibWrapper\PHPSeclibWrapper;
use DP\Core\MachineBundle\Entity\Machine;

/**
 * Machine controller.
 *
 */
class MachineController extends ResourceController
{
    public function testConnectionAction($id)
    {
        if ($this->enableRoleCheck) {
            $this->isGrantedOr403('SHOW');
        }

        $config = $this->getConfiguration();
        $machine = $this->findOr404();

        $test = false;
        $compatLib = false;
        $javaInstalled = false;

        try {
            $secure = PHPSeclibWrapper::getFromMachineEntity($machine);
            $test = $secure->connectionTest();

            $this->getMachineInfos($secure, $machine);
            $is64Bit = $machine->getIs64Bit();

            if ($is64Bit) {
                $compatLib = $secure->hasCompatLib();
            }

            $javaInstalled = $secure->javaInstalled();

            $this->persistAndFlush($machine);
        }
        catch (PHPSeclibWrapper\Exception\ConnectionErrorException $e) {}
        
        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('connection_test.html'))
            ->setData(array(
                $config->getResourceName() => $machine,
                'result' => $test,
                'hasCompatLib' => $compatLib,
                'javaInstalled' => $javaInstalled,
            ))
        ;

        return $this->handleView($view);
    }
    
    private function getMachineInfos(PHPSeclibWrapper $secure, Machine $machine)
    {
        $machine->setHome($secure->getHome());
        $machine->setNbCore($machine->retrieveNbCore());
        $machine->setIs64bit($secure->is64bitSystem());
    }
}

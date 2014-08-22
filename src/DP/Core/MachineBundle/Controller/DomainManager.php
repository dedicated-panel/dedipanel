<?php

namespace DP\Core\MachineBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\DomainManager as BaseDomainManager;
use DP\Core\MachineBundle\Entity\Machine;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;

class DomainManager extends BaseDomainManager
{
    /**
     * @param object $resource
     *
     * @return object|null
     */
    public function delete($resource)
    {
        /** @var ResourceEvent $event */
        $event = $this->dispatchEvent('pre_delete', new ResourceEvent($resource));

        if ($event->isStopped()) {
            $this->flashHelper->setFlash(
                $event->getMessageType(),
                $event->getMessage(),
                $event->getMessageParameters()
            );

            return null;
        }

        $this->manager->remove($resource);
        $this->manager->flush();
        $this->flashHelper->setFlash('success', 'delete');

        /** @var ResourceEvent $event */
        $event = $this->dispatchEvent('post_delete', new ResourceEvent($resource));

        if ($event->isStopped()) {
            $this->flashHelper->setFlash(
                $event->getMessageType(),
                $event->getMessage(),
                $event->getMessageParameters()
            );

            return null;
        }

        return $resource;
    }

    public function connectionTest(Machine $machine)
    {
        $test = $machine->getConnection()->testSSHConnection();

        if (!$test) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.machine.test.failed');

            return false;
        }
        else {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_SUCCESS, 'dedipanel.machine.test.succeeded');
        }

        $conn = $machine->getConnection();
        // Fetches some informations for updating the database record
        $this->updateMachineInfos($machine);

        // Is "screen" installed ?
        if (!$conn->isInstalled('screen')) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.machine.screen_not_installed');
        }

        if ($conn->is64BitSystem() && !$conn->hasCompatLib()) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.machine.compat_lib_not_installed');
        }

        // Is java installed ?
        if (!$conn->isJavaInstalled()) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_WARNING, 'dedipanel.machine.missing_java');
        }
    }

    private function updateMachineInfos(Machine $machine)
    {
        $conn = $machine->getConnection();

        $machine->setHome($conn->getHome());
        $machine->setNbCore($conn->retrieveNbCore());
        $machine->setIs64bit($conn->is64bitSystem());

        $this->manager->persist($machine);
        $this->manager->flush();
    }
}

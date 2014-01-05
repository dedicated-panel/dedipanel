<?php

namespace DP\Core\MachineBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;
use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use DP\Core\MachineBundle\PHPSeclibWrapper\PHPSeclibWrapper;
use DP\Core\MachineBundle\Entity\Machine;

class MachineListener implements EventSubscriberInterface
{    
    public static function getSubscribedEvents()
    {
        return array(
            'dedipanel.machine.create'      => 'createKeyPair', 
            'dedipanel.machine.update'      => 'createKeyPair', 
        );
    }
    
    public function createKeyPair(ResourceEvent $event)
    {
        $machine = $event->getSubject();
        
        if ($machine->getPassword() !== null) {
            $secure = PHPSeclibWrapper::getFromMachineEntity($machine, false);
            
            if ($machine->getPublicKey() !== null) {
                $secure->deleteKeyPair($machine->getPublicKey());
            }
    
            $privkeyFilename = uniqid('', true);
            $pubKey = $secure->createKeyPair($privkeyFilename);
    
            $machine->setPrivateKeyFilename($privkeyFilename);
            $machine->setPublicKey($pubKey);
    
            $this->getMachineInfos($secure, $machine);
        }
    }
    
    private function getMachineInfos(PHPSeclibWrapper $secure, Machine $machine)
    {
        $machine->setHome($secure->getHome());
        $machine->setNbCore($secure->retrieveNbCore());
        $machine->setIs64bit($secure->is64bitSystem());
    }
}

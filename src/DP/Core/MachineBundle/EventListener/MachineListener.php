<?php

namespace DP\Core\MachineBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;
use DP\Core\MachineBundle\PHPSeclibWrapper\PHPSeclibWrapper;
use DP\Core\MachineBundle\Entity\Machine;
use Doctrine\Common\Persistence\ObjectManager;

class MachineListener implements EventSubscriberInterface
{
    private $em;
    
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }
    
    public static function getSubscribedEvents()
    {
        return array(
            'dedipanel.machine.pre_create'  => 'createKeyPair', 
            'dedipanel.machine.pre_update'  => 'createKeyPair', 
            'dedipanel.machine.pre_delete'  => 'deleteKeyPair', 
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
            
            $this->em->persist($machine);
        }
    }
    
    private function getMachineInfos(PHPSeclibWrapper $secure, Machine $machine)
    {
        $machine->setHome($secure->getHome());
        $machine->setNbCore($machine->retrieveNbCore()); // @todo: refacto retrieveNbCore
        $machine->setIs64bit($secure->is64bitSystem());
    }
    
    public function deleteKeyPair(ResourceEvent $event)
    {
        $machine = $event->getSubject();
        
        $secure = PHPSeclibWrapper::getFromMachineEntity($machine);
        $secure->deleteKeyPair($machine->getPublicKey());
        
        foreach ($machine->getGameServers() AS $server) {
            $machine->getGameServers()->removeElement($server);
            $em->remove($server);
        }
    }
}

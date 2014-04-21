<?php

namespace DP\Core\MachineBundle\EventListener;

use Dedipanel\PHPSeclibWrapperBundle\Connection\ConnectionManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;
use Dedipanel\PHPSeclibWrapperBundle\Helper\KeyHelper;
use Dedipanel\PHPSeclibWrapperBundle\Connection\Exception\ConnectionErrorException;

class CRUDListener implements EventSubscriberInterface
{
    private $helper;
    private $manager;
    
    public function __construct(KeyHelper $helper, ConnectionManagerInterface $manager)
    {
        $this->helper = $helper;
        $this->manager = $manager;
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
            try {
                if ($machine->getPrivateKeyName() !== null) {
                    $this->helper->deleteKeyPair($machine);
                }

                $this->helper->createKeyPair($machine);

                $conn = $this->manager->getConnectionFromServer($machine);
                $machine->setHome($conn->getHome());
                $machine->setNbCore($conn->retrieveNbCore());
                $machine->setIs64bit($conn->is64bitSystem());
            }
            catch (ConnectionErrorException $e) {
                $event->stop('machine.connection_problem');
            }
        }
    }
    
    public function deleteKeyPair(ResourceEvent $event)
    {
        try {
            $this->helper->deleteKeyPair($event->getSubject());
        }
        catch (ConnectionErrorException $e) {
            $event->stop('machine.connection_problem');
        }
    }
}

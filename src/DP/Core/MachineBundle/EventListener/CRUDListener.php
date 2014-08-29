<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\MachineBundle\EventListener;

use Dedipanel\PHPSeclibWrapperBundle\Connection\ConnectionManagerInterface;
use Dedipanel\PHPSeclibWrapperBundle\Connection\Exception\ConnectionErrorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;
use Dedipanel\PHPSeclibWrapperBundle\Helper\KeyHelper;

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
            'dedipanel.machine.post_delete' => 'deleteKeyPair',
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
                $event->stop('connection_problem');
            }
        }
    }
    
    public function deleteKeyPair(ResourceEvent $event)
    {
        try {
            $this->helper->deleteKeyPair($event->getSubject());
        }
        catch (ConnectionErrorException $e) {
            $event->stop('cant_delete_public_key', ResourceEvent::TYPE_WARNING);
        }
    }
}

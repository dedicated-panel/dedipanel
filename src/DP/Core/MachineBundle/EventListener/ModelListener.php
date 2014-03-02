<?php

namespace DP\Core\MachineBundle\EventListener;

use Dedipanel\PHPSeclibWrapperBundle\Connection\ConnectionManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DP\Core\MachineBundle\Entity\Machine;

class ModelListener
{
    private $manager;
    
    /**
     * @param \Dedipanel\PHPSeclibWrapperBundle\Connection\ConnectionManagerInterface
     */
    public function __construct(ConnectionManagerInterface $manager)
    {
        $this->manager = $manager;
        
        return $this;
    }
    
    /**
     * Inject the SSH/SFTP Connection into entity
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if ($entity instanceof Machine) {
            $entity->setConnection($this->manager->getConnectionFromServer($entity));
        }
    }
}

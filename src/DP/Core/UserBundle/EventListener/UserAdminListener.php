<?php

namespace DP\Core\UserBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;
use Doctrine\Common\Persistence\ObjectManager;
use DP\Core\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;

class UserAdminListener implements EventSubscriberInterface
{
    private $manager;
    
    public static function getSubscribedEvents()
    {
        return array(
            'dedipanel.user.pre_create' => 'cleanUpdate',
            'dedipanel.user.pre_update' => 'cleanUpdate',  
        );
    }
    
    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;
    }
    
    public function cleanUpdate(ResourceEvent $event)
    {
        $this->manager->updateUser($event->getSubject());
    }
}

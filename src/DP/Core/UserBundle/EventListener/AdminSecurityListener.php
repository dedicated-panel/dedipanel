<?php

namespace DP\Core\UserBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DP\Core\UserBundle\Voter\GroupVoter;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;

class AdminSecurityListener implements EventSubscriberInterface
{
    private $voter;
    
    public static function getSubscribedEvents()
    {
        $events = array();
        
        
        
        return $events;
    }
    
    public function __construct(GroupVoter $voter)
    {
        $this->voter = $voter;
    }
    
    public function filterEvent(ResourceEvent $event)
    {
        
    } 
    
    public function filter(GroupableInterface $object)
    {
        
    }
}

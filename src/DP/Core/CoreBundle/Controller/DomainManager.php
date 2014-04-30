<?php

namespace DP\Core\CoreBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\DomainManager as BaseDomainManager;
use Symfony\Component\EventDispatcher\Event;

class DomainManager extends BaseDomainManager
{
    public function dispatchEvent($name, Event $event)
    {
        /** @var ResourceEvent $event */
        $event = parent::dispatchEvent($name, $event);

        if ($event->isStopped()) {
            $this->flashHelper->setFlash(
                $event->getMessageType(),
                $event->getMessage(),
                $event->getMessageParameters()
            );
        }

        return $event;
    }
}

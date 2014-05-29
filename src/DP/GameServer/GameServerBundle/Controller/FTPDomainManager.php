<?php

namespace DP\GameServer\GameServerBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\DomainManager as BaseDomainManager;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;
use DP\GameServer\GameServerBundle\Entity\GameServer;

class FTPDomainManager extends BaseDomainManager
{
    public function createResource(GameServer $server, $resource)
    {
        /** @var ResourceEvent $event */
        $event = $this->dispatchEvent('pre_create', new ResourceEvent($resource, array('server' => $server)));

        if ($event->isStopped()) {
            $this->flashHelper->setFlash(
                $event->getMessageType(),
                $event->getMessage(),
                $event->getMessageParameters()
            );

            return null;
        }

        $resource->create();

        $this->dispatchEvent('post_create', new ResourceEvent($resource, array('server' => $server)));

        return $resource;
    }

    public function updateResource(GameServer $server, $resource)
    {
        /** @var ResourceEvent $event */
        $event = $this->dispatchEvent('pre_update', new ResourceEvent($resource, array('server' => $server)));

        if ($event->isStopped()) {
            $this->flashHelper->setFlash(
                $event->getMessageType(),
                $event->getMessage(),
                $event->getMessageParameters()
            );

            return null;
        }

        $resource->update();

        $this->dispatchEvent('post_update', new ResourceEvent($resource, array('server' => $server)));

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteResource(GameServer $server, $resource)
    {
        /** @var ResourceEvent $event */
        $event = $this->dispatchEvent('pre_delete', new ResourceEvent($resource, array('server' => $server)));

        if ($event->isStopped()) {
            $this->flashHelper->setFlash(
                $event->getMessageType(),
                $event->getMessage(),
                $event->getMessageParameters()
            );

            return null;
        }

        $resource->delete();

        $this->dispatchEvent('post_delete', new ResourceEvent($resource, array('server' => $server)));

        return $resource;
    }
}

<?php

namespace DP\VoipServer\VoipServerBundle\Controller;

use DP\Core\CoreBundle\Controller\Server\ServerDomainManager;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;

class VoipServerInstanceDomainManager extends ServerDomainManager
{
    /**
     * Finalize the installation if the server is already installed
     *
     * @param ServerInterface $resource
     */
    public function create($resource)
    {
        /** @var ResourceEvent $event */
        $event = $this->dispatchEvent('pre_create', new ResourceEvent($resource));
        $message = $event->getMessage();

        if (!empty($message)) {
            $this->flashHelper->setFlash(
                $event->getMessageType(),
                $event->getMessage(),
                $event->getMessageParameters()
            );
        }

        if (!$this->installationProcess($resource)) {
            return null;
        }

        $this->flashHelper->setFlash(ResourceEvent::TYPE_SUCCESS, 'create');

        /** @var ResourceEvent $event */
        $this->dispatchEvent('post_create', new ResourceEvent($resource));

        $this->manager->persist($resource);
        $this->manager->flush();

        return $resource;
    }
}

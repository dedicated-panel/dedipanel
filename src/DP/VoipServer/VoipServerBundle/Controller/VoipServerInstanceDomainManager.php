<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

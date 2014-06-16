<?php

namespace DP\Core\CoreBundle\Controller\Server;

use Sylius\Bundle\ResourceBundle\Controller\DomainManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DP\Core\CoreBundle\Controller\FlashHelper;
use Sylius\Bundle\ResourceBundle\Controller\Configuration;
use DP\Core\CoreBundle\Model\ServerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;

class ServerDomainManager extends DomainManager
{
    protected $templating;

    /**
     * @{inheritdoc}
     */
    public function __construct(
        ObjectManager $manager,
        EventDispatcherInterface $eventDispatcher,
        FlashHelper $flashHelper,
        Configuration $config,
        $templating
    ) {
        parent::__construct($manager, $eventDispatcher, $flashHelper, $config);

        $this->templating = $templating;
    }

    /**
     * Finalize the installation if the server is already installed
     *
     * @{inheritdoc}
     */
    public function create($resource)
    {
        if ($resource->isAlreadyInstalled()) {
            $resource->setInstallationStatus(100);
        }

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
        if ($event->isStopped() && $event->getMessageType() != ResourceEvent::TYPE_SUCCESS) {
            return null;
        }

        $this->manager->persist($resource);

        $this->dispatchEvent('post_create', new ResourceEvent($resource));

        $this->manager->flush();

        return $resource;
    }

    /**
     * Start/stop/restart a server
     *
     * @param ServerInterface $server
     * @param $state
     * @return ServerInterface|null
     */
    public function changeState(ServerInterface $server, $state)
    {
        /** @var ResourceEvent $event */
        $event = $this->dispatchEvent('pre_change_state', new ResourceEvent($server, array('state' => $state)));

        if ($event->isStopped()) {
            $this->flashHelper->setFlash(
                $event->getMessageType(),
                $event->getMessage(),
                $event->getMessageParameters()
            );

            return null;
        }

        try {
            $server->changeState($state);
        }
        catch (ConnectionErrorException $e) {
            $this->flashHelper->setFlash(
                ResourceEvent::TYPE_ERROR,
                'dedipanel.machine.connection_failed'
            );

            return null;
        }

        $this->flashHelper->setFlash('success', 'dedipanel.flashes.state_changed.' . $state);

        $this->dispatchEvent('post_change_state', $event);

        return $server;
    }
}

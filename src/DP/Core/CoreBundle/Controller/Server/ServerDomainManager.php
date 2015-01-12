<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Controller\Server;

use DP\Core\CoreBundle\Exception\MaxSlotsLimitReachedException;
use DP\Core\CoreBundle\Exception\PortAlreadyInUseException;
use Sylius\Bundle\ResourceBundle\Controller\DomainManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DP\Core\CoreBundle\Controller\FlashHelper;
use Sylius\Bundle\ResourceBundle\Controller\Configuration;
use DP\Core\CoreBundle\Model\ServerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;
use Dedipanel\PHPSeclibWrapperBundle\Connection\Exception\ConnectionErrorException;
use DP\Core\CoreBundle\Exception\InstallAlreadyStartedException;
use DP\Core\CoreBundle\Exception\MissingPacketException;
use DP\Core\CoreBundle\Exception\DirectoryAlreadyExistsException;
use DP\Core\CoreBundle\Exception\MaxServerException;
use DP\VoipServer\VoipServerBundle\Exception\OfflineServerException;
use DP\Core\CoreBundle\Exception\IPBannedException;

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

        $this->manager->persist($resource);
        $this->flashHelper->setFlash(ResourceEvent::TYPE_SUCCESS, 'create');

        /** @var ResourceEvent $event */
        $this->dispatchEvent('post_create', new ResourceEvent($resource));

        if (!$resource->isInstallationEnded()) {
            $this->installationProcess($resource);
            $this->manager->persist($resource);
        }

        $this->manager->flush();

        return $resource;
    }

    /**
     * @param object $resource
     *
     * @return object|null
     */
    public function delete($resource, $fromMachine = false)
    {
        /** @var ResourceEvent $event */
        $event = $this->dispatchEvent('pre_delete', new ResourceEvent($resource));

        if ($event->isStopped()) {
            $this->flashHelper->setFlash(
                $event->getMessageType(),
                $event->getMessage(),
                $event->getMessageParameters()
            );

            return null;
        }

        $flash = 'delete';

        if ($fromMachine && $this->deleteFromMachine($resource)) {
            $flash = 'full_delete';
        }

        $this->manager->remove($resource);
        $this->manager->flush();
        $this->flashHelper->setFlash(ResourceEvent::TYPE_SUCCESS, $flash);

        $this->dispatchEvent('post_delete', new ResourceEvent($resource));

        return $resource;
    }

    private function deleteFromMachine(ServerInterface $resource)
    {
        try {
            if (!$resource->deleteServer()) {
                $this->flashHelper->setFlash('error', 'dedipanel.machine.delete_failed');

                return false;
            }
        }
        catch (ConnectionErrorException $e) {
            $this->flashHelper->setFlash('error', 'dedipanel.machine.connection_failed');

            return false;
        }

        return true;
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

        $this->flashHelper->setFlash(ResourceEvent::TYPE_SUCCESS, 'dedipanel.flashes.state_changed.' . $state);

        return $server;
    }

    protected function installationProcess(ServerInterface $server)
    {
        $progress = $server->getInstallationStatus();

        if ($progress === null && !$this->install($server)) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.core.install_failed');

            return false;
        }

        if ($progress != 100) {
            $progress = $server->getInstallationProgress();
            $server->setInstallationStatus($progress);
        }

        if ($progress == 100) {
            if (!$this->finalizeInstall($server)) {
                $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.core.post_install_failed');

                return false;
            }

            $this->flashHelper->setFlash(ResourceEvent::TYPE_SUCCESS, 'dedipanel.flashes.finalize_install_server');
        }

        return true;
    }

    /**
     * Launch a server installation
     *
     * @param ServerInterface $server
     */
    protected function install(ServerInterface $server)
    {
        $installed = false;

        try {
            $installed = $server->installServer($this->templating);
        }
        catch (InstallAlreadyStartedException $e) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.core.installAlreadyStarted');
        }
        catch (MissingPacketException $e) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.core.missingPacket');
        }
        catch (ConnectionErrorException $e) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.machine.connection_failed');
        }
        catch (MaxServerException $e) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.core.max_server_limit');
        }
        catch (PortAlreadyInUseException $e) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.core.port_in_use');
        }
        catch (MaxSlotsLimitReachedException $e) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.voip.max_slots');
        }
        catch (OfflineServerException $e) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.voip.offline_server');
        }
        catch (IPBannedException $e) {
            $params = [];
            $duration = $e->getDuration();

            if (!empty($duration)) {
                $params['%duration%'] = $duration;
            }

            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.voip.banned_from_server');
        }

        return $installed;
    }

    /**
     * Finalize a server installation
     *
     * @param ServerInterface $server
     */
    protected function finalizeInstall(ServerInterface $server)
    {
        $finalized = false;

        try {
            $finalized = $server->finalizeInstallation($this->templating);
        }
        catch (ConnectionErrorException $e) {
            $this->flashHelper->setFlash(ResourceEvent::TYPE_ERROR, 'dedipanel.machine.connection_failed');
        }

        return $finalized;
    }
}

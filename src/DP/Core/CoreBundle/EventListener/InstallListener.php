<?php

namespace DP\Core\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;
use DP\Core\CoreBundle\Model\ServerInterface;
use Dedipanel\PHPSeclibWrapperBundle\Connection\Exception\ConnectionErrorException;
use DP\Core\CoreBundle\Exception\InstallAlreadyStartedException;
use DP\Core\CoreBundle\Exception\MissingPacketException;
use DP\Core\CoreBundle\Exception\DirectoryAlreadyExistsException;

class InstallListener implements EventSubscriberInterface
{
    /** @var \Doctrine\Common\Persistence\ObjectManager $manager */
    private $manager;
    private $templating;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager, $templating)
    {
        $this->manager = $manager;
        $this->templating = $templating;
    }

    /**
     * @{inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'dedipanel.steam.post_fetch_logs'  => 'fetchLogs',
            'dedipanel.steam.post_create' => 'install',
            'dedipanel.steam.post_fetch_install_progress' => 'install',
            'dedipanel.steam.post_fetch_install_progress' => 'finalizeInstall',

            'dedipanel.minecraft.post_fetch_logs'  => 'fetchLogs',
            'dedipanel.minecraft.post_create' => 'install',
            'dedipanel.minecraft.post_fetch_install_progress' => 'install',
            'dedipanel.minecraft.post_fetch_install_progress' => 'finalizeInstall',

            'dedipanel.teamspeak.pre_create' => 'install',
            'dedipanel.teamspeak.post_fetch_install_progress' => 'install',
        );
    }

    /**
     * Fetch the installation progress
     *
     * @param ResourceEvent $event
     * @return null
     */
    public function fetchLogs(ResourceEvent $event)
    {
        /** @var AbstractServer $server */
        $server = $event->getSubject();

        if (!$server->isInstallationEnded()) {
            try {
                // Update the installation progression
                // And finalize it if necessary
                $progress = $server->getInstallationProgress();
                $server->setInstallationStatus($progress);

                if ($progress == 100) {
                    $this->finalizeInstall($event);
                }
            }
            catch (ConnectionErrorException $e) {
                $event->stop('dedipanel.machine.connection_failed', ResourceEvent::TYPE_ERROR);
            }

            $this->manager->persist($server);
            $this->manager->flush();
        }
    }

    /**
     * Launch a server installation
     *
     * @param ResourceEvent $event
     */
    public function install(ResourceEvent $event)
    {
        /** @var GameServer $server */
        $server = $event->getSubject();

        if ($server->getInstallationStatus() == null) {
            try {
                $server->installServer($this->templating);
                $event->stop('dedipanel.flashes.install_server', ResourceEvent::TYPE_SUCCESS);
            }
            catch (InstallAlreadyStartedException $e) {
                $event->stop('dedipanel.core.installAlreadyStarted', ResourceEvent::TYPE_ERROR);
            }
            catch (MissingPacketException $e) {
                $event->stop('dedipanel.core.missingPacket', ResourceEvent::TYPE_ERROR);
            }
            catch (ConnectionErrorException $e) {
                $event->stop('dedipanel.machine.connection_failed', ResourceEvent::TYPE_ERROR);
            }
            catch (DirectoryAlreadyExistsException $e) {
                $event->stop('dedipanel.core.directory_exists', ResourceEvent::TYPE_ERROR);
            }
        }
    }

    /**
     * Finalize a server installation
     *
     * @param ResourceEvent $event
     */
    public function finalizeInstall(ResourceEvent $event)
    {
        /** @var GameServer $server */
        $server = $event->getSubject();

        if ($server->getInstallationStatus() == 100) {
            try {
                $server->finalizeInstallation($this->templating);
                $event->stop('dedipanel.flashes.finalize_install_server', ResourceEvent::TYPE_SUCCESS);
            }
            catch (ConnectionErrorException $event) {
                $event->stop('dedipanel.machine.connection_failed', ResourceEvent::TYPE_ERROR);
            }
        }
    }
}

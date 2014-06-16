<?php

namespace DP\Core\CoreBundle\EventListener;

use DP\Core\CoreBundle\Exception\IPBannedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;
use DP\Core\CoreBundle\Model\ServerInterface;
use Dedipanel\PHPSeclibWrapperBundle\Connection\Exception\ConnectionErrorException;
use DP\Core\CoreBundle\Exception\InstallAlreadyStartedException;
use DP\Core\CoreBundle\Exception\MissingPacketException;
use DP\Core\CoreBundle\Exception\DirectoryAlreadyExistsException;
use Symfony\Component\HttpFoundation\RequestStack;
use DP\Core\CoreBundle\Exception\MaxServerException;

class InstallListener implements EventSubscriberInterface
{
    /** @var \Doctrine\Common\Persistence\ObjectManager $manager */
    private $manager;
    private $templating;
    private $request;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager, $templating, RequestStack $stack)
    {
        $this->manager = $manager;
        $this->templating = $templating;
        $this->request = $stack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'dedipanel.steam.post_fetch_logs'  => array('fetchLogs', 'ensurePersisted'),
            'dedipanel.steam.post_create' => array('install', 'ensurePersisted'),
            'dedipanel.steam.post_fetch_install_progress' => array('install', 'finalizeInstall', 'ensurePersisted'),

            'dedipanel.minecraft.post_fetch_logs'  => array('fetchLogs', 'ensurePersisted'),
            'dedipanel.minecraft.post_create' => array('install', 'ensurePersisted'),
            'dedipanel.minecraft.post_fetch_install_progress' => array('install', 'finalizeInstall', 'ensurePersisted'),

            'dedipanel.teamspeak.pre_create'  => array('install', 'fetchLogs', 'finalizeInstall', 'ensurePersisted'),
            'dedipanel.teamspeak.pre_delete'  => 'delete',

            'dedipanel.teamspeak.instance.pre_create' => array('install', 'ensurePersisted'),
            'dedipanel.teamspeak.instance.pre_delete' => 'delete',
        );
    }

    /**
     * Launch a server installation
     *
     * @param ResourceEvent $event
     */
    public function install(ResourceEvent $event)
    {
        /** @var ServerInterface $server */
        $server = $event->getSubject();

        if ($server->getInstallationStatus() == null) {
            $installed = false;

            try {
                $installed = $server->installServer($this->templating);
            }
            catch (InstallAlreadyStartedException $e) {
                $event->stop('dedipanel.core.installAlreadyStarted', ResourceEvent::TYPE_ERROR);

                return false;
            }
            catch (MissingPacketException $e) {
                $event->stop('dedipanel.core.missingPacket', ResourceEvent::TYPE_ERROR);

                return false;
            }
            // TODO: phpseclib wrapper bundle
            catch (DirectoryAlreadyExistsException $e) {
                $event->stop('dedipanel.core.directory_exists', ResourceEvent::TYPE_ERROR);

                return false;
            }
            catch (ConnectionErrorException $e) {
                $event->stop('dedipanel.machine.connection_failed', ResourceEvent::TYPE_ERROR);

                return false;
            }
            catch (MaxServerException $e) {
                $event->stop('dedipanel.core.max_server_limit', ResourceEvent::TYPE_ERROR);

                return false;
            }
            catch (OfflineServerException $e) {
                $event->stop('dedipanel.voip.offline_server', ResourceEvent::TYPE_ERROR);

                return false;
            }
            catch (IPBannedException $e) {
                $params = [];
                $duration = $e->getDuration();

                if (!empty($duration)) {
                    $params['%duration%'] = $duration;
                }

                $event->stop('dedipanel.voip.banned_from_server', ResourceEvent::TYPE_ERROR, $params);

                return false;
            }

            if (!$installed) {
                $event->stop('dedipanel.core.install_failed', ResourceEvent::TYPE_ERROR);

                return false;
            }
        }

        $this->callNext($event, 'install');
    }

    /**
     * Fetch the installation progress
     *
     * @param ResourceEvent $event
     * @return null
     */
    public function fetchLogs(ResourceEvent $event)
    {
        /** @var ServerInterface $server */
        $server = $event->getSubject();

        if (!$server->isInstallationEnded()) {
            try {
                // Update the installation progression
                // And finalize it if necessary
                $progress = $server->getInstallationProgress();
                $server->setInstallationStatus($progress);
            }
            catch (ConnectionErrorException $e) {
                $event->stop('dedipanel.machine.connection_failed', ResourceEvent::TYPE_ERROR);
            }
        }

        $this->callNext($event, 'fetchLogs');
    }

    /**
     * Finalize a server installation
     *
     * @param ResourceEvent $event
     */
    public function finalizeInstall(ResourceEvent $event)
    {
        /** @var ServerInterface $server */
        $server = $event->getSubject();

        if ($server->getInstallationStatus() == 100) {
            try {
                $finalized = $server->finalizeInstallation($this->templating);

                $event->stop('dedipanel.flashes.finalize_install_server', ResourceEvent::TYPE_SUCCESS);
            }
            catch (ConnectionErrorException $e) {
                $event->stop('dedipanel.machine.connection_failed', ResourceEvent::TYPE_ERROR);

                return;
            }

            if (!$finalized) {
                $event->stop('dedipanel.core.post_install_failed', ResourceEvent::TYPE_ERROR);

                return;
            }
        }

        $this->callNext($event, 'finalizeInstall');
    }

    public function delete(ResourceEvent $event)
    {
        /** @var ServerInterface $server */
        $server = $event->getSubject();

        if (!empty($this->request) && $this->request->query->get('fromMachine') == true) {
            try {
                if (!$server->deleteServer()) {
                    $event->stop('dedipanel.machine.delete_failed', ResourceEvent::TYPE_ERROR);
                }
            }
            catch (ConnectionErrorException $e) {
                $event->stop('dedipanel.machine.connection_failed', ResourceEvent::TYPE_ERROR);
            }
        }
    }

    public function ensurePersisted(ResourceEvent $event)
    {
        /** @var ServerInterface $server */
        $server = $event->getSubject();

        $this->manager->persist($server);
        $this->manager->flush();
    }

    private function callNext(ResourceEvent $event, $type)
    {
        $name   = $event->getName();
        $events = $this->getSubscribedEvents()[$name];

        if (is_array($events)) {
            $keys   = array_keys($events, $type);
            $events = array_slice($events, array_pop($keys)+1);

            if (!empty($events)) {
                call_user_func(array($this, array_shift($events)), $event);
            }
        }
    }
}

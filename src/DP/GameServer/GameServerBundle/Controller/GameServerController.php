<?php

/**
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\GameServerBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use DP\GameServer\GameServerBundle\Entity\GameServer;
use DP\GameServer\GameServerBundle\Exception\InstallAlreadyStartedException;
use PHPSeclibWrapper\Exception\MissingPacketException;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;
use DP\GameServer\GameServerBundle\Exception\NotImplementedException;
use Symfony\Component\HttpFoundation\Request;
use DP\GameServer\GameServerBundle\Exception\InvalidPathException;

class GameServerController extends ResourceController
{
    public function create($resource)
    {
        if ($resource->isAlreadyInstalled()) {
            $resource->setInstallationStatus(100);
        }
        
        $event = $this->dispatchEvent('pre_create', $resource);
        if (!$event->isStopped()) {
            $this->persistAndFlush($resource);
        }
        
        return $event;
    }
        
    public function installAction()
    {
        if ($this->enableRoleCheck) {
            if (!$this->isGranted('CREATE') && !$this->isGranted('UPDATE')) {
                throw new AccessDeniedException;
            }
        }
        
        $server = $this->findOr404();
        $status = $server->getInstallationStatus();
        
        $event = $this->dispatchEvent('pre_install', $server);
        if ($event->isStopped()) {
            $this->setFlash($event->getMessageType(), $event->getMessage(), $event->getMessageParams());
        }
        else {
            try {
                if ($status == 100) {
                    $server->finalizeInstallation($this->get('twig'));
                }
                elseif ($status < 100) {
                    $status = $server->getInstallationProgress();
                    $server->setInstallationStatus($status);
                    
                    if ($status == 100) {
                        $server->finalizeInstallation($this->get('twig'));
                    }
                }
                
                if ($status === null) {
                    $server->installServer($this->get('twig'));
                }
            }
            catch (InstallAlreadyStartedException $e) {
                $trans = $this->get('translator')->trans('game.installAlreadyStarted');
                $this->setFlash('error', $trans);
            }
            catch (MissingPacketException $e) {
                $trans = $this->get('translator')->trans('steam.missingCompatLib');
                $this->setFlash('error', $trans);
            }
            
            $this->persistAndFlush($server, 'install');
        }
        
        return $this->redirectToIndex();
    }
    
    public function changeStateAction(Request $request)
    {
        $this->isGrantedOr403('STATE');
        
        $server = $this->findOr404();
        $state = $request->get('state');
        
        $event = new ResourceEvent($server, array('state' => $state));
        $this->dispatchEvent('pre_change_state', $event);
        if ($event->isStopped()) {
            $this->setFlash($event->getMessageType(), $event->getMessage(), $event->getMessageParams());
        }
        else {
            $server->changeStateServer($state);
            $this->dispatchEvent('change_state', $event);
            $this->dispatchEvent('post_change_state', $event);
        }
        
        $this->get('session')->getFlashBag()->add('stateChanged', 'steam.stateChanged.' . $state);
        
        return $this->redirectToIndex();
    }
    
    public function regenAction()
    {
        $this->isGrantedOr403('ADMIN');
        
        $server = $this->findOr404();
        
        $event = $this->dispatchEvent('pre_regen', $server);
        if ($event->isStopped()) {
            $this->setFlash($event->getMessageType(), $event->getMessage(), $event->getMessageParams());
        }
        else {
            $this->dispatchEvent('regen', $server);
            $server->regenerateScripts($this->get('twig'));
            $this->dispatchEvent('post_regen', $server);
        }
        
        return $this->redirectTo($server);
    }
    
    public function showLogsAction($id)
    {
        $this->isGrantedOr403('ADMIN');
        
        $config = $this->getConfiguration();   
        $server = $this->findOr404();
        
        $this->dispatchEvent('pre_show_log', $server);
        
        if ($server->isInstallationEnded()) {
            $logs = $server->getServerLogs();
        }
        else {
            $logs = $server->getInstallLogs();
            
            // Met automatiquement les logs à jour dans le cas d'une installation
            $status = $server->getInstallationProgress();
            $server->setInstallationStatus($status);
            
            $this->persistAndFlush($server, 'show_log');
        }
        
        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('logs.html'))
            ->setData(array(
                $config->getResourceName() => $server,
                'logs'                     => $logs
            ))
        ;

        return $this->handleView($view);
    }

    public function rconAction(Request $request)
    {
        $this->isGrantedOr403('RCON');
        
        $config = $this->getConfiguration();
        $server = $this->findOr404();
        
        $logs = $server->getServerLogs() . "\n";
        $form = $this->createRconForm();
        
        $online = $server->getQuery()->isOnline();
        $banned = $server->getQuery()->isBanned();
        
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            if ($online && !$banned) {
                $data = $form->getData();
                $cmd = $data['cmd'];
                
                // Exécution de la commande
                $ret = $server
                    ->setRcon($server->getRcon())
                    ->sendCmd($cmd)
                ;

                $logs .= '> ' . $cmd . "\n" . $ret . "\n";
                
                if ($config->isApiRequest()) {
                    return $this->handleView($this->view(array('log' => $logs, 'cmd' => $cmd, 'ret' => $ret)));
                }
            }
            else {
                $this->setFlash('error', 'server offline');
            }
        }
        
        if ($config->isApiRequest()) {
            return $this->handleView($this->view($form));
        }

        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('rcon.html'))
            ->setData(array(
                $config->getResourceName() => $server,
                'form'                     => $form->createView(), 
                'log'                      => $logs,
            ))
        ;

        return $this->handleView($view);
    }
    
    public function createRconForm(array $default = array())
    {
        $form = $this
            ->createFormBuilder($default)
            ->add('cmd', 'text', array('label' => 'game.rcon.command'))
        ;

        return $form->getForm();
    }
    
    public function pluginAction()
    {
        $this->isGrantedOr403('PLUGIN');
        
        $config = $this->getConfiguration();
        $server = $this->findOr404();
        
        $pluginId = $this->getRequest()->get('pluginId');
        $action   = $this->getRequest()->get('action');
        
        if ($pluginId && $action) {
            $em = $this->getDoctrine()->getEntityManager();
            $plugin = $em->getRepository('DPGameBundle:Plugin')->findOneBy(array('id' => $pluginId));
            
            if (!$plugin) {
                throw new NotFoundHttpException('Requested plugin does not exist.');
            }
            
            if ($action == 'install') {
                $event = $this->installPlugin($server, $plugin);
            }
            elseif ($action == 'uninstall') {
                $event = $this->uninstallPlugin($server, $plugin); 
            }
            else {
                throw new NotImplementedException();
            }
            
            if (!$event->isStopped()) {
                $this->setFlash('success', 'plugin_' . $action);
            
                return $this->redirectToRoute(
                    $config->getRedirectRoute('plugin'),
                    array('id' => $server->getId())
                );
            }

            $this->setFlash($event->getMessageType(), $event->getMessage(), $event->getMessageParams());
        }
        
        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('plugn.html'))
            ->setData(array(
                $config->getResourceName() => $server,
            ))
        ;

        return $this->handleView($view);
    }
        
    protected function installPlugin(GameServer $server, Plugin $plugin)
    {
        $event = new ResourceEvent($server, array('plugin' => $plugin));
        $event = $this->dispatchEvent('pre_install_plugin', $event);
        
        try {
            $server->installPlugin($this->get('twig'), $plugin);
            $server->addPlugin($plugin);
        }
        catch (MissingPacketException $e) {
            $event->stop('game.missingPacket', ResourceEvent::TYPE_ERROR, array('%plugin%' => strval($plugin), '%packet%' => $e->getPacketList()));
        }
        catch (NotImplementedException $e) {
            $event->stop('game.cant_install_plugin', ResourceEvent::TYPE_ERROR);
        }
        
        if (!$event->isStopped()) {
            $manager = $this->getManager();
    
            $manager->persist($server);
            $this->dispatchEvent('install_plugin', $event);
            $manager->flush();
            $this->dispatchEvent('post_install_plugin', $event);
        }
        
        return $event;
    }
    
    protected function uninstallPlugin(GameServer $server, Plugin $plugin)
    {
        $event = new ResourceEvent($server, array('plugin' => $plugin));
        $event = $this->dispatchEvent('pre_uninstall', $event);
        
        try {
            $server->uninstallPlugin($this->get('twig'), $plugin);
            $server->removePlugin($plugin);
        }
        catch (NotImplementedException $e) {
            $event->stop('game.cant_install_plugin', ResourceEvent::TYPE_ERROR);
        }
        
        if (!$event->isStopped()) {
            $manager = $this->getManager();
    
            $manager->persist($server);
            $this->dispatchEvent('uninstall_plugin', $event);
            $manager->flush();
            $this->dispatchEvent('post_uninstall_plugin', $event);
        }
        
        return $event;
    }
}

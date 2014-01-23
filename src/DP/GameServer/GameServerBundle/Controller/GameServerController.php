<?php

/**
 * (c) Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\GameServerBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use DP\GameServer\GameServerBundle\Entity\GameServer;

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
            if (!$this->isGranted('ADD') && !$this->isGranted('EDIT')) {
                throw new AccessDeniedException;
            }
        }
        
        $server = $this->findOr404();
        $status = $server->getInstallationStatus();
        
        try {
            if ($status == 100) {
                $server->finalizeInstallation($this->get('twig'));
            }
            elseif ($status < 100) {
                $status = $server->getInstallationProgress();
                $server->setInstallationStatus($newStatus);
                
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
            $trans;
        }
        catch (MissingPacketException $e) {
            $trans = $this->get('translator')->trans('steam.missingCompatLib');
            $this->set('error', $trans);
        }

        $event = $this->dispatchEvent('pre_install', $server);
        if ($event->isStopped()) {
            $this->setFlash($event->getMessageType(), $event->getMessage(), $event->getMessageParams());
        }
        else {
            $this->persistAndFlush($server, 'install');
        }
        
        return $this->redirectToIndex();
    }
    
    public function changeStateAction($state)
    {
        $this->isGrantedOr403('STATE');
        
        $server = $this->findOr404();
        
        $event = $this->dispatchEvent('pre_change_state', $server);
        if ($event->isStopped()) {
            $this->setFlash($event->getMessageType(), $event->getMessage(), $event->getMessageParams());
        }
        else {
            $this->dispatchEvent('change_state', $resource);
            $server->changeStateServer($state);
            $this->dispatchEvent('post_change_state', $resource);
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
            $this->dispatchEvent('regen', $resource);
            $server->regenerateScripts($this->get('twig'));
            $this->dispatchEvent('post_regen', $resource);
        }
        
        return $this->redirectTo($server);
    }
    
    public function showLogsAction($id)
    {
        $this->isGrantedOr403('ADMIN');
        
        $server = $this->findOr404();
        
        if ($server->getInstallationStatus() == 101) {
            $logs = $server->getServerLogs();
        }
        else {
            $logs = $server->getInstallLogs();
        }
        
        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('logs.html'))
            ->setData(array(
                $config->getResourceName() => $resource,
                'logs'                     => $logs
            ))
        ;

        return $this->handleView($view);
    }
}

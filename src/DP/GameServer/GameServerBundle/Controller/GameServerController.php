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
use DP\GameServer\GameServerBundle\Exception\InstallAlreadyStartedException;
use PHPSeclibWrapper\Exception\MissingPacketException;

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

    public function rconAction(Request $request)
    {
        $config = $this->getConfiguration();
        
        $this->isGrantedOr403('RCON');
        $server = $this->findOr404();
        
        $logs = $server->getServerLogs() . "\n";
        $ret = '';
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
            ->setTemplate($config->getTemplate('console.html'))
            ->setData(array(
                $config->getResourceName() => $resource,
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
}

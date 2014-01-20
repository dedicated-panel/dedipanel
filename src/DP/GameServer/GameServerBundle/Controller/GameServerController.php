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
    public function installAction()
    {
        if ($this->enableRoleCheck) {
            if (!$this->isGranted('ADD') && !$this->isGranted('EDIT')) {
                throw new AccessDeniedException;
            }
        }
        
        $server = $this->findOr404();
        $status = $server->getInstallationStatus();
        
        if ($status == 100) {
            $server->finalizeInstallation($this->get('twig'));
        }
        elseif ($status < 100) {
            $newStatus = $server->getInstallationProgress();
            $server->setInstallationStatus($newStatus);
            
            if ($newStatus == 100) {
                $server->finalizeInstallation($this->get('twig'));
            }
            elseif ($newStatus === null) {
                $server->installServer($this->get('twig'));
            }
        }
        elseif ($status === null) {
            $server->installServer($this->get('twig'));
        }

        $em->persist($server);
        $em->flush();

        return $this->redirectToIndex();
    }

    public function changeStateAction($state)
    {
        $this->isGrantedOr403('STATE');
        
        $server = $this->findOr404();
        $server->changeStateServer($state);
        
        $this->get('session')->getFlashBag()->add('stateChanged', 'steam.stateChanged.' . $state);
        
        return $this->redirectToIndex();
    }

    public function regenAction()
    {
        $this->isGrantedOr403('ADMIN');
        
        $server = $this->findOr404();

        $twig = $this->get('twig');
        $server->regenerateScripts($twig);
        
        return $this->redirectTo($server);
    }
    
    public function showLogAction($id)
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

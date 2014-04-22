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
use DP\GameServer\GameServerBundle\Exception\NotImplementedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use DP\GameServer\GameServerBundle\Controller\FlashHelper;

class GameServerController extends ResourceController
{
    /**
     * @var FlashHelper
     */
    protected $flashHelper;
    /**
     * @var DomainManager
     */
    protected $domainManager;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        if ($container !== null) {
            $this->flashHelper = new FlashHelper(
                $this->config,
                $container->get('translator'),
                $container->get('session')
            );
            $this->domainManager = new DomainManager(
                $container->get($this->config->getServiceName('manager')),
                $container->get('event_dispatcher'),
                $this->flashHelper,
                $this->config,
                $container->get('twig')
            );
        }
    }

    public function installAction(Request $request)
    {
        if (!$this->isGranted('CREATE', $this->find($request)) && !$this->isGranted('UPDATE', $this->find($request))) {
            throw new AccessDeniedException;
        }
        
        $server = $this->findOr404($request);
        $this->domainManager->install($server);
        
        return $this->redirectHandler->redirectToIndex();
    }
    
    public function changeStateAction(Request $request)
    {
        $this->isGrantedOr403('STATE', $this->find($request));
        
        $server = $this->findOr404($request);
        $state = $request->get('state');
        $this->domainManager->changeState($server, $state);
        
        return $this->redirectHandler->redirectToReferer();
    }
    
    public function regenAction(Request $request)
    {
        $this->isGrantedOr403('ADMIN');
        
        $server = $this->findOr404($request);
        $this->domainManager->regenerateConfig($server);

        return $this->redirectHandler->redirectToReferer();
    }
    
    public function showLogsAction(Request $request)
    {
        $this->isGrantedOr403('ADMIN', $this->find($request));
        
        $config = $this->getConfiguration();   
        $server = $this->findOr404($request);

        $logs = $this->domainManager->getLogs($server);

        if ($logs === null) {
            return $this->redirectHandler->redirectToReferer();
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
        $this->isGrantedOr403('RCON', $this->find($request));
        
        $config = $this->getConfiguration();
        $server = $this->findOr404($request);
        $form = $this->createRconForm();
        
        $logs = $server->getServerLogs() . "\n";

        if (!$this->domainManager->isAccessibleFromQuery($server)) {
            return $this->redirectHandler->redirectToReferer();
        }

        if ($request->isMethod('POST') && $form->submit($request)->isValid()) {
            $data = $form->getData();
            $cmd = $data['cmd'];

            $ret = $this->domainManager->executeRconCmd($server, $cmd);
            $logs .= '> ' . $cmd . "\n" . $ret . "\n";

            if ($config->isApiRequest()) {
                return $this->handleView($this->view(array('log' => $logs, 'cmd' => $cmd, 'ret' => $ret)));
            }
        }
        
        if ($config->isApiRequest()) {
            return $this->handleView($this->view(array('form' => $form, 'log' => $logs)));
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
    
    public function pluginAction(Request $request)
    {
        $this->isGrantedOr403('PLUGIN', $this->find($request));
        
        $config = $this->getConfiguration();
        $server = $this->findOr404($request);

        $pluginId = $request->get('plugin');
        $action   = $request->get('action');
        
        if ($pluginId && $action) {
            $em = $this->getDoctrine()->getManager();
            $plugin = $em->getRepository('DPGameBundle:Plugin')->findOneBy(array('id' => $pluginId));

            if (!$plugin) {
                throw new NotFoundHttpException('Requested plugin does not exist.');
            }
            if ($action != 'install' && $action != 'uninstall') {
                throw new NotImplementedException();
            }

            $method = $action . 'Plugin';
            $server = $this->domainManager->{$method}($server, $plugin);

            return $this->redirectHandler->redirectToReferer();
        }
        
        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('plugin.html'))
            ->setData(array(
                $config->getResourceName() => $server,
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

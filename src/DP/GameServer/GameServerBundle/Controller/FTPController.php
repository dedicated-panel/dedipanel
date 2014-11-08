<?php

/**
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\GameServerBundle\Controller;

use DP\Core\CoreBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use DP\GameServer\GameServerBundle\Entity\GameServer;
use Dedipanel\PHPSeclibWrapperBundle\SFTP\AbstractItem;
use Dedipanel\PHPSeclibWrapperBundle\SFTP\Directory;
use Dedipanel\PHPSeclibWrapperBundle\SFTP\File;
use Dedipanel\PHPSeclibWrapperBundle\SFTP\Exception\InvalidPathException;

/**
 * @todo: Apply criteria & sorting
 * @todo: refacto phpseclib
 */
class FTPController extends ResourceController
{
    const TYPE_FILE = 'file';
    const TYPE_DIRECTORY  = 'directory';

    /**
     * @var FTPDomainManager
     */
    protected $domainManager;


    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        if ($container !== null) {
            $this->domainManager = new FTPDomainManager(
                $container->get($this->config->getServiceName('manager')),
                $container->get('event_dispatcher'),
                $this->flashHelper,
                $this->config
            );
        }
    }

    /**
     * {@inheritdoc}
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function indexAction(Request $request)
    {
        throw new NotFoundHttpException();
    }
    
    /**
     * {@inheritdoc}
     */
    public function showAction(Request $request)
    {
        $this->isGrantedOr403('FTP', $this->find($request));
        
        $config = $this->getConfiguration();
        /** @var GameServer $server */
        $server = $this->findOr404($request);

        $resource = $this->getResource($server, $request->get('path'));

        if ($resource instanceof File) {
            throw new MethodNotAllowedHttpException(array('POST', 'PUT'));
        }
        
        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('show.html'))
            ->setData(array(
                'server' => $server,
                'resource' => $resource,
            ))
        ;

        return $this->handleView($view);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request)
    {
        $this->isGrantedOr403('CREATE');
        
        $config = $this->getConfiguration();
        $server = $this->findOr404($request);

        $resource = $this->createResource($server,
            $request->get('path'),
            $request->get('type')
        );
        $form     = $this->getForm($resource);
        
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $resource = $this->domainManager->createResource($server, $resource);

                return $this->redirectTo($server, $resource);
            }
        }
        
        if ($config->isApiRequest()) {
            return $this->handleView($this->view($form));
        }
        
        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('create.html'))
            ->setData(array(
                'server'   => $server,
                'form'     => $form->createView(),
                'resource' => $resource,
            ))
        ;
        
        return $this->handleView($view);
    }

    /**
     * Display the form for editing or update the resource.
     */
    public function updateAction(Request $request)
    {
        $this->isGrantedOr403('UPDATE', $this->find($request));

        $config = $this->getConfiguration();
        $server = $this->findOr404($request);
        
        $resource = $this->getResource($server, $request->get('path'));
        $form     = $this->getForm($resource);
        
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $resource = $this->domainManager->updateResource($server, $resource);

                return $this->redirectTo($server, $resource);
            }
        }

        if ($config->isApiRequest()) {
            return $this->handleView($this->view($form));
        }

        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('update.html'))
            ->setData(array(
                'server'   => $server,
                'form'     => $form->createView(),
                'resource' => $resource,
            ))
        ;

        return $this->handleView($view);
    }

    /**
     * Delete resource.
     */
    public function deleteAction(Request $request)
    {
        $this->isGrantedOr403('DELETE', $this->find($request));
        
        $server   = $this->findOr404($request);
        $resource = $this->getResource($server, $request->get('path'));
        
        if ($request->request->get('confirmed', false)) {
            $resource = $this->domainManager->deleteResource($server, $resource);

            if ($request->isXmlHttpRequest()) {
                return JsonResponse::create(array('id' => $request->get('id')));
            }

            $this->flashHelper->setFlash('success', 'delete');

            $config = $this->getConfiguration();
            $parameters = $config->getRedirectParameters();

            if (empty($parameters)) {
                $parameters['id'] = $server->getId();
                $parameters['path'] = $resource->getPath();
            }

            return $this->redirectHandler->redirectToRoute(
                $config->getRedirectRoute('show'),
                $parameters
            );
        }

        if ($request->isXmlHttpRequest()) {
            throw new AccessDeniedHttpException;
        }

        $view = $this
            ->view()
            ->setTemplate($request->attributes->get('template', 'SyliusWebBundle:Backend/Misc:delete.html.twig'))
            ->setData(array(
                'server'   => $server,
                'resource' => $resource,
            ))
        ;

        return $this->handleView($view);
    }

    /**
     * Get a new resource
     *
     * @param GameServer $server
     * @param string $path
     * @param string $type
     * @return Directory|File
     * @throws \RuntimeException
     */
    public function createResource(GameServer $server, $path, $type)
    {
        if ($type != self::TYPE_FILE && $type != self::TYPE_DIRECTORY) {
            throw new \RuntimeException('Not supported ftp resource type.');
        }

        $conn    = $server->getMachine()->getConnection();
        $gameDir = $server->getAbsoluteGameContentDir();
        $path    = '~/' . $path;

        if ($type == self::TYPE_FILE) {
            $item = new File($conn, $path, $gameDir, true);
        }
        else {
            $item = new Directory($conn, $path, $gameDir, true);
        }

        return $item;
    }

    /**
     * @param GameServer $server
     * @param string $path
     * @return Dedipanel\PHPSeclibWrapperBundle\SFTP\AbstractItem
     */
    public function getResource(GameServer $server, $path)
    {
        $path = '~/' . $path;

        /** @var Dedipanel\PHPSeclibWrapperBundle\SFTP\SFTPItemFactory $factory */
        $factory  = $this->get('dedipanel.sftp_factory');

        try {
            $resource = $factory->getItem(
                $server->getMachine()->getConnection(),
                $path,
                $server->getAbsoluteGameContentDir()
            );
        }
        catch (InvalidPathException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $resource;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getForm($resource = null)
    {
        if ($resource instanceof File) {
            $formType = 'dedipanel_game_ftp_file';
        }
        elseif ($resource instanceof Directory) {
            $formType = 'dedipanel_game_ftp_directory';
        }
        else {
            throw new \RuntimeException('Not supported ftp resource type.');
        }
        
        return $this->createForm($formType, $resource);
    }
    
    /**
     * {@inheritdoc}
     */
    public function redirectTo(GameServer $server, AbstractItem $resource)
    {
        $config = $this->getConfiguration();

        $parameters = $config->getRedirectParameters();
        $route = $config->getRedirectRoute('show');
        
        if (empty($parameters)) {
            $parameters['id'] = $server->getId();
            $parameters['path'] = $resource->getRelativePath();

            if ($resource instanceof File) {
                $parameters['path'] = $resource->getPath();
            }
        }

        return $this->redirectHandler->redirectToRoute($route, $parameters);
    }
}

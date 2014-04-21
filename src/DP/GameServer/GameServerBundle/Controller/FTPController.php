<?php

/**
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\GameServerBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use DP\GameServer\GameServerBundle\FTP\File;
use DP\GameServer\GameServerBundle\FTP\Directory;
use DP\GameServer\GameServerBundle\FTP\AbstractItem;
use DP\GameServer\GameServerBundle\Exception\InvalidPathException;

/**
 * @todo: Apply criteria & sorting
 * @todo: refacto phpseclib
 */
class FTPController extends ResourceController
{
    /** @var \DP\GameServer\GameServerBundle\Entity\GameServer $server **/
    private $server;
    
    const TYPE_FILE = 'file';
    const TYPE_DIRECTORY  = 'directory';
    
    
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
        $this->isGrantedOr403('FTP');
        
        $config = $this->getConfiguration();
        $this->server = $this->findOr404($request);
        
        $path = $request->get('path');
        $resource = $this->getResource($path);
        $content = $resource->getContent();
        
        if ($resource instanceof Directory) {
            $files = array_filter($content, function ($el) {
                return $el instanceof File;
            });
            $dirs = array_filter($content, function ($el) {
                return $el instanceof Directory;
            });
            
            $content = array('files' => $files, 'dirs' => $dirs);
        }
        
        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('show.html'))
            ->setData(array(
                'server' => $this->server,
                'path' => $resource->getFullPath(), 
                'content' => $content, 
                'invalid' => $resource->isInvalid(), 
                'previous_path' => dirname($resource->getFullPath()), 
                'type' => ($resource instanceof File) ? self::TYPE_FILE : self::TYPE_DIRECTORY, 
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
        $this->server = $this->findOr404($request);
        
        $type = $request->get('type');
        $path = $request->get('path');
        
        $resource = $this->createResource($path, $type);
        $form     = $this->getForm($resource);
        
        if ($resource->isInvalid()) {
            throw new NotFoundHttpException(sprintf('Requested %s does not exist', $resource->getFullPath()));
        }
        
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $event = $this->create($resource);
            if (!$event->isStopped()) {
                $this->setFlash('success', 'create');
                
                return $this->redirectTo($resource);
            }
            
            $this->setFlash($event->getMessageType(), $event->getMessage(), $event->getMessageParams());
        }
        
        if ($config->isApiRequest()) {
            return $this->handleView($this->view($form));
        }
        
        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('create.html'))
            ->setData(array(
                'server' => $this->server,
                'form'   => $form->createView(), 
                'path'   => $path, 
                'type'   => $type, 
                'previous_path' => dirname($path), 
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
        $this->server = $this->findOr404($request);
        
        $path = $request->get('path');
        $resource = $this->getResource($path);
        $form     = $this->getForm($resource);
        
        if ($resource->isInvalid()) {
            throw new NotFoundHttpException(sprintf('Requested %s does not exist', $resource->getFullPath()));
        }
        
        $oldPath = $resource->getFullPath();
        
        if (($request->isMethod('PUT') || $request->isMethod('POST')) && $form->bind($request)->isValid()) {
            $event = $this->update($resource, $oldPath);
            if (!$event->isStopped()) {
                $this->setFlash('success', 'update');

                return $this->redirectTo($resource);
            }

            $this->setFlash($event->getMessageType(), $event->getMessage(), $event->getMessageParams());
        }

        if ($config->isApiRequest()) {
            return $this->handleView($this->view($form));
        }

        $view = $this
            ->view()
            ->setTemplate($config->getTemplate('update.html'))
            ->setData(array(
                'server'  => $this->server,
                'form'    => $form->createView(), 
                'path'    => $path,  
                'invalid' => $resource->isInvalid(),  
                'type'    => ($resource instanceof File ? self::TYPE_FILE : self::TYPE_DIRECTORY), 
                'previous_path' => dirname($path),
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
        
        $this->server = $this->findOr404($request);
        
        $path = $request->get('path');
        $resource = $this->getResource($path);
        
        if ($resource->isInvalid()) {
            throw new NotFoundHttpException(sprintf('Requested %s does not exist', $resource->getFullPath()));
        }
        
        if ($request->request->get('confirmed', false)) {
            $event = $this->delete($resource);

            if ($request->isXmlHttpRequest()) {
                return JsonResponse::create(array('id' => $request->get('id')));
            }

            if ($event->isStopped()) {
                $this->setFlash($event->getMessageType(), $event->getMessage(), $event->getMessageParams());

                return $this->redirectTo($resource);
            }

            $this->setFlash('success', 'delete');

            $config = $this->getConfiguration();
            $parameters = $config->getRedirectParameters();
            
            if (empty($parameters)) {
                $parameters['id'] = $this->server->getId();
                $parameters['path'] = $resource->getPath();
            }
            
            return $this->redirectToRoute(
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
                'server'  => $this->server, 
                'invalid' => $resource->isInvalid(), 
                'previous_path' => dirname($path), 
            ))
        ;

        return $this->handleView($view);
    }
    
    public function createResource($path, $type)
    {
        if ($type != self::TYPE_FILE && $type != self::TYPE_DIRECTORY) {
            throw new \RuntimeException('Not supported ftp resource type.');
        }
        
        if ($type == self::TYPE_FILE) {
            return new File($path);
        }
        else {
            return new Directory($path);
        }
    }
    
    /**
     * Get the ressource from its type, path and name
     * If a full path (path + name) is provided, 
     * the file/directory content is retrieved from the server
     *
     * @param string      $path
     * @return File|Directory
     */
    public function getResource($path)
    {
        $name = null;
        $type = null;
        $item = null;
        
        list($path, $name, $type) = $this->retrievePathStat($path);
        
        if ($type != self::TYPE_FILE && $type != self::TYPE_DIRECTORY) {
            throw new \RuntimeException('Not supported ftp resource type.');
        }
        
        if ($type == self::TYPE_FILE) {
            $item = new File($path, $name);
        }
        else {
            $item = new Directory($path, $name);
        }
        
        $this->retrieveContent($item);
        
        return $item;
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
    public function create($resource)
    {
        $event = $this->dispatchEvent('pre_create', $resource);
        if (!$event->isStopped()) {
            $this->dispatchEvent('create', $resource);
            
            if ($resource instanceof File) {
                $this->server->uploadFile($resource->getFullPath(), $resource->getContent());
            }
            elseif ($resource instanceof Directory) {
                $this->server->createDirectory($resource->getFullPath());
            }
            else {
                throw new \RuntimeException('Not supported ftp resource type.');
            }
            
            $this->dispatchEvent('post_create', $resource);
        }

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function update($resource, $oldPath)
    {
        $event = $this->dispatchEvent('pre_update', $resource);
        if (!$event->isStopped()) {
            $this->dispatchEvent('update', $resource);
            
            $oldPath = $this->server->getAbsoluteGameContentDir() . $oldPath;
            $newPath = $this->server->getAbsoluteGameContentDir() . $resource->getFullpath();
            
            if ($oldPath != $newPath) {
                $this->server->rename($oldPath, $newPath);
            }
            
            if ($resource instanceof File) {
                $this->server->uploadFile($resource->getFullPath(), $resource->getContent());
            }
            
            $this->dispatchEvent('post_update', $resource);
        }

        return $event;
    }
    
    /**
     * {@inheritdoc}
     */
    public function delete($resource)
    {
        $event = $this->dispatchEvent('pre_delete', $resource);
        if (!$event->isStopped()) {
            $this->dispatchEvent('delete', $resource);
            
            $this->server->remove($resource->getFullPath());
            
            $this->dispatchEvent('post_delete', $resource);
        }

        return $event;
    }
    
    /**
     * {@inheritdoc}
     */
    public function redirectTo($resource)
    {
        $config = $this->getConfiguration();
        $parameters = $config->getRedirectParameters();
        
        if (empty($parameters)) {
            $parameters['id'] = $this->server->getId();
            $parameters['path'] = $resource->getFullPath();
            
            if ($resource instanceof File) {
                $parameters['path'] = $resource->getPath();
            }
        }

        return $this->redirectToRoute(
            $config->getRedirectRoute('show'),
            $parameters
        );
    }
    
    public function retrievePathStat($path)
    {
        $sftp = $this->server->getMachine()->getConnection()->getSFTP();
        $stat = $sftp->stat($this->server->getAbsoluteGameContentDir() . $path);
        
        $type = null;
        $name = null;
        
        if (!empty($stat)) {
            $type = ($stat['type'] == 1) ? self::TYPE_FILE : self::TYPE_DIRECTORY;
            
            $pathinfo = pathinfo($path);
            
            $path = $pathinfo['dirname'];
            $name = $pathinfo['basename'];
        }
        else {
            return false;
        }
        
        return array($path, $name, $type);
    }
    
    public function retrieveContent(AbstractItem $item)
    {
        $content = '';
        
        try {
            $content = $this->server->getContent($item);
            
            if ($item instanceof Directory) {
                $temp = array();
                
                foreach ($content['files'] AS $file) {
                    $file = new File($item->getFullPath(), $file['name']);
                    $temp[] = $file;
                }
                foreach ($content['dirs'] AS $dir) {
                    $dir = new Directory($item->getFullPath(), $dir['name']);
                    $temp[] = $dir;
                }
                
                $content = $temp;
            }
        }
        catch (InvalidPathException $e) {
            $item->setInvalid(true);
        }
        
        $item->setContent($content);
        
        return $item;
    }
}

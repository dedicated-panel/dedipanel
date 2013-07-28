<?php

/*
** Copyright (C) 2010-2013 Kerouanton Albin, Smedts Jérôme
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along
** with this program; if not, write to the Free Software Foundation, Inc.,
** 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace DP\GameServer\GameServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use DP\GameServer\GameServerBundle\Exception\InvalidPathException;

abstract class FTPController extends Controller
{
    abstract public function getEntityRepository();
    abstract public function getBaseRoute();
    
    public function showAction($id, $path)
    {
        $server = $this->getEntityRepository()->find($id);
        $dirContent = array('files' => array(), 'dirs' => array());
        $invalid = false;
        
        if (!$server) {
            throw $this->createNotFoundException('Unable to find GameServer entity.');
        }
        
        if (!empty($path) && strrpos($path, '/') != strlen($path)-1) {
            $path .= '/';
        }
        
        try {
            $dirContent = $server->getDirContent($path);
        }
        catch (InvalidPathException $e) {
            $invalid = true;
        }
        
        return $this->render('DPGameServerBundle:FTP:show.html.twig', array(
            'sid' => $id, 
            'currentPath' => $path, 
            'prevDirPath' => dirname($path), 
            'dirContent' => $dirContent, 
            'baseRoute' => $this->getBaseRoute(), 
            'del_form' => $this->createDeleteForm($id, $path)->createView(), 
            'invalid' => $invalid, 
        ));
    }
    
    public function editFileAction($id, $path)
    {
        $server = $this->getEntityRepository()->find($id);
        
        if (!$server) {
            throw $this->createNotFoundException('Unable to find GameServer entity.');
        }
        
        $filename = basename($path);
        $fileContent = $server->getFileContent($path);
        
        $form = $this->createEditFileForm(array('filename' => $filename, 'file' => $fileContent));
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            
            if ($form->isValid()) {
                $data = $form->getData();
                $server->uploadFile($path, $data['file']);
            }
        }
        
        return $this->render('DPGameServerBundle:FTP:editFile.html.twig', array(
            'sid' => $id, 
            'form' => $form->createView(), 
            'path' => $path, 
            'dirPath' => dirname($path), 
            'baseRoute' => $this->getBaseRoute(), 
            'del_form' => $this->createDeleteForm($id, $path)->createView(), 
        ));
    }
    
    public function createFileAction($id, $path = '')
    {
        $server = $this->getEntityRepository()->find($id);
        
        if (!$server) {
            throw $this->createNotFoundException('Unable to find GameServer entity.');
        }
        
        $form = $this->createEditFileForm(array('filename' => '', 'file' => ''));
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            
            if ($form->isValid()) {
                $data = $form->getData();
                $filepath = $path . $data['filename'];
                
                if (!$server->fileExists($filepath)) {
                    $server->uploadFile($filepath, $data['file']);

                    return $this->redirect($this->generateUrl(
                            $this->getBaseRoute() . '_ftp_show', 
                            array('id' => $id, 'path' => $path)
                    ));
                }
                else {
                    $form->get('filename')->addError(new FormError('game.ftp.fileAlreadyExists'));
                }
            }
        }
        
        return $this->render('DPGameServerBundle:FTP:addFile.html.twig', array(
            'sid' => $id, 
            'form' => $form->createView(), 
            'path' => $path, 
            'baseRoute' => $this->getBaseRoute(), 
        ));
    }
    
    public function deleteAction($id, $path)
    {
        $server = $this->getEntityRepository()->find($id);
        
        if (!$server) {
            throw $this->createNotFoundException('Unable to find GameServer entity.');
        }
        
        // Suppression du fichier/dossier
        $server->remove($path);
        
        return $this->redirect($this->generateUrl(
                $this->getBaseRoute() . '_ftp_show', 
                array('id' => $id, 'path' => dirname($path))
        ));
    }
    
    public function createDirectoryAction($id, $path = '')
    {
        $server = $this->getEntityRepository()->find($id);
        
        if (!$server) {
            throw $this->createNotFoundException('Unable to find GameServer entity.');
        }
        
        $form = $this->createAddDirForm(array('dirname' => ''));
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            
            if ($form->isValid()) {
                $data = $form->getData();
                $dirpath = $path . $data['dirname'];
                
                if (!$server->dirExists($dirpath)) {
                    $server->createDirectory($dirpath);

                    return $this->redirect($this->generateUrl(
                            $this->getBaseRoute() . '_ftp_show', 
                            array('id' => $id, 'path' => $dirpath)
                    ));
                }
                else {
                    $form->get('dirname')->addError(new FormError('game.ftp.dirAlreadyExists'));
                }
            }
        }
        
        return $this->render('DPGameServerBundle:FTP:addDirectory.html.twig', array(
            'sid' => $id, 
            'form' => $form->createView(), 
            'path' => $path, 
            'baseRoute' => $this->getBaseRoute(), 
        ));
    }
    
    private function createEditFileForm(array $default = array())
    {
        return $this->createFormBuilder($default)
            ->add('filename', 'text', array('label' => 'game.ftp.filename'))
            ->add('file', 'textarea', array('label' => 'game.ftp.content'))
            ->getForm()
        ;
    }

    private function createDeleteForm($id, $path)
    {
        return $this->createFormBuilder(array('id' => $id, 'path' => $path))
            ->add('id', 'hidden')
            ->add('path', 'hidden')
            ->getForm()
        ;
    }
    
    private function createAddDirForm()
    {
        return $this->createFormBuilder(array())
            ->add('dirname', 'text', array('label' => 'game.ftp.dirname'))
            ->getForm()
        ;
    }
}

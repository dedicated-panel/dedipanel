<?php

/*
** Copyright (C) 2010-2012 Kerouanton Albin, Smedts Jérôme
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

abstract class FTPController extends Controller
{
    abstract public function getEntityRepository();
    abstract public function getBaseRoute();
    
    public function showAction($id, $path)
    {
        $server = $this->getEntityRepository()->find($id);
        
        if (!$server) {
            throw $this->createNotFoundException('Unable to find GameServer entity.');
        }
        
        if (!empty($path) && strrpos($path, '/') != strlen($path)-1) {
            $path .= '/';
        }
        
        $dirContent = $server->getDirContent($path);
        
        return $this->render('DPGameServerBundle:FTP:show.html.twig', array(
            'sid' => $id, 
            'currentPath' => $path, 
            'dirContent' => $dirContent, 
            'baseRoute' => $this->getBaseRoute(), 
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
            $form->bindRequest($request);
            
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
        ));
    }
    
    public function createEditFileForm(array $default = array())
    {
        return $this->createFormBuilder($default)
                    ->add('filename', 'text', array('label' => 'game.ftp.filename'))
                    ->add('file', 'textarea', array('label' => 'game.ftp.content'))
                    ->getForm()
        ;
    }
}

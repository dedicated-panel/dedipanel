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

namespace DP\GameServer\SteamServerBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DP\Core\MachineBundle\PHPSeclibWrapper\PHPSeclibWrapper;

class CfgController extends Controller
{
    public function showAction($id, $path)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $server = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);
        
        if (!$server) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }
        
        if (!empty($path) && strrpos($path, '/') != strlen($path)-1) {
            $path .= '/';
        }
        
        $dirContent = $server->getDirContent($path);
        
        return $this->render('DPSteamServerBundle:Cfg:show.html.twig', array(
            'sid' => $id, 
            'currentPath' => $path, 
            'dirContent' => $dirContent, 
        ));
    }
    
    public function editFileAction($id, $path)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getEntityManager();
        $server = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);
        
        if (!$server) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }
        
        $filename = $this->getFilename($path);
        $fileContent = $server->getFileContent($path);
        
        $default = array('filename' => $filename, 'file' => $fileContent);
        $form = $this->createFormBuilder($default)
                     ->add('filename', 'text', array('label' => 'steam.cfg.filename'))
                     ->add('file', 'textarea', array('label' => 'steam.cfg.content'))
                     ->getForm();
        
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            $data = $form->getData();
            
            $server->uploadFile($path, $data['file']);
        }
        
        return $this->render('DPSteamServerBundle:Cfg:editFile.html.twig', array(
            'sid' => $id, 
            'form' => $form->createView(), 
            'path' => $path, 
        ));
    }
    
    private function getFilename($path)
    {
        $pos = strrpos($path, '/');
        
        if ($pos) {
            $path = substr($path, $pos+1);
        }
        
        
        return $path;
    }
}

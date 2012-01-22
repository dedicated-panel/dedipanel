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
use DP\GameServer\SteamServerBundle\Entity\SteamServer;

class PluginsController extends Controller
{
    public function showServerAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }
        
        $intersectCallback = function ($plugin1, $plugin2) {
            return $plugin1->getId() - $plugin2->getId();
        };
        $plugins = $entity->getGame()->getPlugins();
        $notInstalled = array_udiff($plugins, $entity->getPlugins(), $intersectCallback);
        
        return $this->render('DPSteamServerBundle:Plugins:show.html.twig', array(
            'entity' => $entity, 
            'notInstalledPlugins' => $notInstalled
        ));
    }
    
    public function installAction($id, $plugin)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }
        
        return $this->render('DPSteamServerBundle:Plugins:install.html.twig', array(
            'entity' => $entity
        ));
    }
    
    public function uninstallAction($id, $plugin)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }
        
        return $this->render('DPSteamServerBundle:Plugins:uninstall.html.twig', array(
            'entity' => $entity
        ));
    }
}
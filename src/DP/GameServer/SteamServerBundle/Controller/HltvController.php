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

class HltvController extends Controller
{
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $serv = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);
        
        if (!$serv) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }
        
        $status = $serv->getHltvStatus();
        
        return $this->render('DPSteamServerBundle:Hltv:show.html.twig', array(
            'id' => $id, 
            'status' => $status, 
            'form' => $this->createStartForm($serv)->createView(), 
        ));
    }
    
    public function startAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $serv = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);
        $request = $this->get('request');
        
        if (!$serv) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }
        
        $request = $this->get('request');
        $form = $this->createStartForm($serv);
        $form->bindRequest($request);
        $data = $form->getData();

        $serv->startHltv($data['port'], $data['servIp'], 
            $data['servPort'], $data['password'], $data['record']);
        
        return $this->redirect($this->generateUrl('steam_hltv_show', array('id' => $id)));
    }
    
    public function stopAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $serv = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);
        
        if (!$serv) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }
        
        $serv->stopHltv();
        
        return $this->redirect($this->generateUrl('steam_hltv_show', array('id' => $id)));
    }
    
    public function createStartForm(\DP\GameServer\SteamServerBundle\Entity\SteamServer $serv)
    {
        $default = array(
            'ip' => $serv->getMachine()->getPublicIp(), 
            'servIp' => $serv->getMachine()->getPublicIp(), 
            'servPort' => $serv->getPort());
        $form = $this->createFormBuilder($default)
                    ->add('ip', 'text', array('label' => 'steam.hltv.hltvIP', 'read_only' => true))
                    ->add('port', 'integer')
                    ->add('password', 'password', array('label' => 'steam.hltv.password', 'required' => false))
                    ->add('servIp', 'text', array('label' => 'steam.hltv.serverAddress'))
                    ->add('servPort', 'integer')
                    ->add('record', 'text', array('label' => 'steam.hltv.recordName', 'required' => false))
                    ->getForm();
        ;
        
        return $form;
    }
}

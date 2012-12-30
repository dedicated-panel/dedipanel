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
use Symfony\Component\HttpFoundation\Response;

class RconController extends Controller
{
    public function consoleJsonAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $server = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);
        
        if (!$server) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }

        $response = new Response;
        $response->setCharset('utf-8');
        $response->headers->set('Content-type', 'application/json');
        
        $jsonResp = array();
        $trans = $this->get('translator');
        
        if ($server->query->isOnline() && !$server->query->isBanned()) {
            $form = $this->createRconForm($server, array('password' => $server->getRconPassword()));
            $request = $this->get('request');
            
            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);

                if ($form->isValid()) {
                    $data = $form->getData();

                    // Enregistrement du mdp rcon
                    $server->setRconPassword($data['password']);
                    $em->persist($server);
                    $em->flush();

                    $server->setRcon($this->getRconFromServer($server));
                    $ret = $server->getRcon()->sendCmd($data['cmd']);
                    
                    $jsonResp['log'] = '> ' . $data['cmd'] . "\n" . $ret . "\n";
                }
            }

        }
        elseif ($server->query->isBanned()) {
            $jsonResp['error'] = $trans->trans('game.banned');
        }
        else {
            $jsonResp['error'] = $trans->trans('game.offline');
        }
        
        $response->setContent(json_encode($jsonResp));
        
        return $response;
    }
    
    public function consoleAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $server = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);
        
        if (!$server) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }
        
        $log = '';
        $form = $this->createRconForm($server, array('password' => $server->getRconPassword()));
        
        if ($server->getQuery()->isOnline() && !$server->getQuery()->isBanned()) {
            $request = $this->get('request');

            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);

                if ($form->isValid()) {
                    $data = $form->getData();

                    // Enregistrement du mdp rcon
                    $server->setRconPassword($data['password']);
                    $em->persist($server);
                    $em->flush();

                    $server->setRcon($this->getRconFromServer($server));
                    $ret = $server->getRcon()->sendCmd($data['cmd']);

                    $log = '> ' . $data['cmd'] . "\n" . $ret . "\n";
                }
            }
        }
        
        return $this->render('DPSteamServerBundle:Rcon:console.html.twig', array(
            'sid' => $id, 
            'log' => $log, 
            'form' => $form->createView(), 
            'online' => $server->getQuery()->isOnline(), 
            'banned' => $server->getQuery()->isBanned(), 
        ));
    }
    
    public function createRconForm(\DP\GameServer\SteamServerBundle\Entity\SteamServer $serv, array $default = array())
    {
        $form = $this->createFormBuilder($default)
                    ->add('cmd', 'text', array('label' => 'game.command'))
                    ->add('password', 'text', array('label' => 'steam.rcon'))
        ;
        
        return $form->getForm();
    }
    
    public function getRconFromServer(\DP\GameServer\SteamServerBundle\Entity\SteamServer $server)
    {
        if ($server->getGame()->isSource()) {
            $rconFactory = $this->get('rcon.source');
        }
        else {
            $rconFactory = $this->get('rcon.goldsrc');
        }
        
        return $rconFactory->getRcon(
                $server->getMachine()->getPublicIp(), 
                $server->getPort(), 
                $server->getRconPassword()
        );
    }
}

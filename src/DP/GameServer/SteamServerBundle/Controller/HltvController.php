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

namespace DP\GameServer\SteamServerBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use DP\GameServer\SteamServerBundle\SteamQuery\Exception\UnexpectedServerTypeException;

class HltvController extends Controller
{
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $serv = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);
        
        if (!$serv) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }
        
        $hltv = $this->get('query.steam')->getServerQuery(
            $serv->getMachine()->getPublicIp(), 
            $serv->getHltvPort(), 
            true
        );

        // On vérifie le statut de l'hltv
        // S'il n'est pas en ligne, on regarde sur le port du serv
        // Si l'ip a été bannie
        $status = $hltv->isOnline();
        $banned = false;
        if (!$status) {
            $banned = $hltv->isBanned();
        }
        
        $notHltv = true;
        try {
            $notHltv = !$hltv->verifyStatus();
        }
        catch (UnexpectedServerTypeException $e) {
            $status = false;
        }
        
        return $this->render('DPSteamServerBundle:Hltv:show.html.twig', array(
            'id' => $id, 
            'status' => $status, 
            'banned' => $banned, 
            'notHltv' => $notHltv, 
            'form' => ($status ? null : $this->createStartForm($serv)->createView()), 
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
        $data = array('servIp' => '', 'servPort' => '', 'password' => '', 'record' => '', 'reload' => '');
        
        if ($form->isValid()) {
            $data += $form->getData();

            if ($serv->getGame()->isSource()) {
                if ($serv->isEmptyRconPassword()) {
                    $serv->setRconPassword($data['rconPasswd']);
                }
                
                // Les serveurs hltv source se démarre en rcon
                // Les serveurs hltv goldsrc se démarre en ssh
                $serv->setRcon($this->get('rcon.source')->getRcon(
                    $serv->getMachine()->getPublicIp(), 
                    $serv->getPort(), 
                    $serv->getRconPassword()
                ));
            }
            
            $serv->setHltvPort($data['port']);                
            $serv->startHltv(
                    $data['servIp'], $data['servPort'], 
                    $data['password'], $data['record'], $data['reload']);
            
            $em->persist($serv);
            $em->flush();
        }
        
        return $this->redirect($this->generateUrl('steam_hltv_show', array('id' => $id)));
    }
    
    public function stopAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $serv = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);
        
        if (!$serv) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }
        
        if ($serv->getGame()->isSource()) {
            // Les serveurs hltv source sont exécutés via des commandes rcon, 
            // Alors que les serveurs hltv goldsrc sont exécutés en ssh
            $serv->setRcon($this->get('rcon.source')->getRcon(
                $serv->getMachine()->getPublicIp(), 
                $serv->getPort(), 
                $serv->getRconPassword()
            ));
        }
        
        $serv->stopHltv();
        
        return $this->redirect($this->generateUrl('steam_hltv_show', array('id' => $id)));
    }
    
    public function createStartForm(\DP\GameServer\SteamServerBundle\Entity\SteamServer $serv)
    {
        $default = array(
            'ip' => $serv->getMachine()->getPublicIp(), 
            'servIp' => $serv->getMachine()->getPublicIp(), 
            'servPort' => $serv->getPort(), 
            'port' => $serv->getHltvPort(), 
        );
        $form = $this->createFormBuilder($default)
                    ->add('ip', 'text', array('label' => 'steam.hltv.hltvIP', 'read_only' => true))
                    ->add('port', 'integer');
        
        // Ajout d'un champ "mot de passe du serveur"
        // Pour les jeux GoldSrc, puisqu'il faut connaître le mdp du serv
        // Pour pouvoir y connecter l'hltv
        if (!$serv->getGame()->isSource()) {
            $form->add('password', 'password', array('label' => 'steam.hltv.password', 'required' => false));
        }
        
        // Ajout d'un champ "Mdp RCON" pour les jeux Source, 
        // Afin de pouvoir envoyer les requêtes RCON nécessaires
        if ($serv->getGame()->isSource() && $serv->isEmptyRconPassword()) {
            $form->add('rconPasswd', 'text', array('label' => 'steam.rcon.password', 'required' => true));
        }
        
        $form->add('servIp', 'text', array('label' => 'steam.hltv.serverAddress'))
            ->add('servPort', 'integer')
            ->add('record', 'text', array('label' => 'steam.hltv.recordName', 'required' => false))
            ->add('reload', 'choice', array(
                'label' => 'steam.hltv.reloadMap', 'required' => false, 
                'multiple' => false, 'expanded' => true, 'choices' => array(
                    '0' => 'steam.hltv.yes', 
                    '1' => 'steam.hltv.no'
            )));
        
        return $form->getForm();
    }
}

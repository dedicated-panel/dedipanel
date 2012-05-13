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
        
        $hltv = $this->get('query.steam')->getServerQuery(
            $serv->getMachine()->getPublicIp(), 
            $serv->getHltvPort()
        );

        // On vérifie le statut de l'hltv
        // S'il n'est pas en ligne, on regarde sur le port du serv
        // Si l'ip a été bannie
        $status = false;
        $banned = false;
        if (!$hltv->isOnline()) {
            $servQuery = $this->get('query.steam')->getServerQuery(
                $serv->getMachine()->getPublicIp(), 
                $serv->getPort()
            );
            $banned = $servQuery->isBanned();
        }
        else {
            $status = $hltv->isOnline();
        }
        
        return $this->render('DPSteamServerBundle:Hltv:show.html.twig', array(
            'id' => $id, 
            'status' => $status, 
            'banned' => $banned, 
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
        
        if ($form->isValid()) {
            $data = $form->getData();

            $serv->setHltvPort($data['port']);

            if ($serv->getGame()->isSource()) {
                if ($serv->isEmptyRconPassword()) {
                    $serv->setRconPassword($data['rconPasswd']);
                }
                
                $rcon = $this->get('query.steam')->getRcon(
                    $serv->getMachine()->getPublicIp(), 
                    $serv->getPort(), 
                    $serv->getRconPassword()
                );
                
                $exec = $rcon->sendCmd('exec hltv.cfg');
                
                if ($exec !== false && $data['reload'] == true) {
                    $reload = $rcon->sendCmd('reload');
                }
            }
            else {            
                $serv->startHltv($data['servIp'], 
                    $data['servPort'], $data['password'], $data['record']);
            }
            
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
            $form->add('rconPasswd', 'password', array('label' => 'steam.rcon.password', 'required' => true));
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

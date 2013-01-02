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

namespace DP\GameServer\MinecraftServerBundle\Controller;

use DP\GameServer\GameServerBundle\Controller\RconController as BaseRconController;
use DP\GameServer\GameServerBundle\Entity\GameServer;
use DP\GameServer\MinecraftServerBundle\Entity\MinecraftServer;

class RconController extends BaseRconController
{
    public function getEntityRepository()
    {
        return $this->getDoctrine()->getEntityManager()->getRepository('DPMinecraftServerBundle:MinecraftServer');
    }
    
    public function getRconFromServer(GameServer $server)
    {
        if (!$server instanceof MinecraftServer)
        {
            throw new Exception('The requested server is not a MinecraftServer.');
        }
        
        return $this->get('rcon.source')->getRcon(
                $server->getMachine()->getPublicIp(), 
                $server->getRconPort(), 
                $server->getRconPassword()
        );
    }
    
    public function getFormActionURL(GameServer $server)
    {
        if (!$server instanceof MinecraftServer) {
            throw new Exception('The requested server is not a MinecraftServer.');
        }
        
        return $this->generateUrl('minecraft_rcon_execute', array('id' => $server->getId()));
    }
    
    public function createRconForm(array $default = array())
    {
        $form = parent::createRconForm($default);
        
        return $form
                    ->add('rconPort', 'integer', array('label' => 'minecraft.rconPort'))
        ;
    }
    
    public function getFormDefaultValues(GameServer $server)
    {
        if (!$server instanceof MinecraftServer) {
            throw new Exception('The requested server is not a MinecraftServer.');
        }
        
        $default = parent::getFormDefaultValues($server);
        
        return $default + array('rconPort' => $server->getRconPort());
    }
    
    public function saveServerData(GameServer $server, array $data)
    {
        if (!$server instanceof MinecraftServer) {
            throw new Exception('The requested server is not a MinecraftServer.');
        }
        
        $server->setRconPort($data['rconPort']);
        
        return parent::saveServerData($server, $data);
    }
}

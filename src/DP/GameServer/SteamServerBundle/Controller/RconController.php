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

use DP\GameServer\GameServerBundle\Controller\RconController as BaseRconController;
use DP\GameServer\GameServerBundle\Entity\GameServer;
use DP\GameServer\SteamServerBundle\Entity\SteamServer;

class RconController extends BaseRconController
{
    public function getEntityRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('DPSteamServerBundle:SteamServer');
    }
    
    public function getRconFromServer(GameServer $server)
    {
        if (!$server instanceof SteamServer) {
            throw new Exception('The requested server is not a SteamServer.');
        }
        
        return $server->getRcon();
    }
    
    public function getBaseRoute()
    {
        return 'steam';
    }
}

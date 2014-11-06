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

namespace DP\GameServer\SteamServerBundle\Service\PacketFactory;

use DP\GameServer\GameServerBundle\Service\PacketFactory;

/**
 * @author Albin Kerouanton
 */
class SourceRconPacketFactory extends PacketFactory
{
    public $SERVERDATA_EXECCOMMAND = 2;
    public $SERVERDATA_AUTH = 3;
    public $SERVER_RESPONSE_VALUE = 0;
    public $SERVER_AUTH_RESPONSE = 2;
    
    public function getAuthPacket(&$id, $passwd)
    {
        return $this->forgePacket($id, $this->SERVERDATA_AUTH, $passwd);
    }
    
    public function getCmdPacket(&$id, $cmd)
    {
        return $this->forgePacket($id, $this->SERVERDATA_EXECCOMMAND, $cmd);
    }
    
    public function getEmptyResponsePacket(&$id)
    {
        return $this->forgePacket($id, $this->SERVER_RESPONSE_VALUE, '');
    }
    
    /**
     * @param integer $cmdType
     */
    private function forgePacket(&$id, $cmdType, $cmd)
    {
        $id = mt_rand(0, pow(2, 16));
        $packet = $this->newPacket(
            self::transformLong($id) .
            self::transformLong($cmdType) .
            $cmd . chr(0) . chr(0)
        );
        
        return $packet->pushContent($this->transformLong($packet->getLength()));
    }
}

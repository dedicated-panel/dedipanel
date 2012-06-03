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

namespace DP\GameServer\SteamServerBundle\Service;
use DP\GameServer\GameServerBundle\Service\PacketFactory;

/**
 * @author Albin Kerouanton
 */
class SteamQueryPacketFactory extends PacketFactory
{
    const HEADER = "\xFF\xFF\xFF\xFF"; //0xFFFFFFFF;
    const A2A_PING = "\x69"; //0x69;
    const A2S_INFO = "TSource Engine Query\0";
    const A2S_PLAYER = "\x55"; //0x55;
    const A2S_RULES = "\x56"; //0x56;
    
    /**
     * Get a ping request packet
     * 
     * @return Packet
     */
    public function A2A_PING()
    {
//        return $this->newPacket(self::HEADER . self::A2A_PING);
        return $this->A2S_INFO();
    }
    
    /**
     * Get a server infos request packet
     * 
     * @return Packet
     */
    public function A2S_INFO()
    {
        return $this->newPacket(self::HEADER . self::A2S_INFO);
    }
    
    /**
     * Get a players request packet
     * 
     * @return Packet
     */
    public function A2S_PLAYER($challenge)
    {
        $challenge = $this->transformLong($challenge);
        
        return $this->newPacket(self::HEADER . self::A2S_PLAYER . $challenge);
    }
    
    /**
     * Get a rules request packet
     * 
     * @return Packet
     */
    public function A2S_RULES($challenge)
    {
        $challenge = $this->transformLong($challenge);
        
        return $this->newPacket(self::HEADER . self::A2S_RULES . $challenge);
    }
    
    /**
     * Get a challenge packet
     * 
     * @return Packet 
     */
    public function A2S_SERVERQUERY_GETCHALLENGE()
    {
        return $this->A2S_PLAYER(-1);
    }
}
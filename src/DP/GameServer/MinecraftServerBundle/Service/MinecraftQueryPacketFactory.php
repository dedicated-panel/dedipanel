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

namespace DP\GameServer\MinecraftServerBundle\Service;

use DP\GameServer\GameServerBundle\Service\PacketFactory;

/**
 * @author Albin Kerouanton
 */
class MinecraftQueryPacketFactory extends PacketFactory
{
    const HEADER = "\xFE\xFD"; //0xFEFD
    const HANDSHAKE = "\x09"; // 0x09
    const STAT = "\x00";
    
    /**
     * Get an handshake packet
     * 
     * @return Packet
     */
    public function handshake($sessionId)
    {
        $sessionId = $this->transformLong($sessionId);
        
        return $this->newPacket(self::HEADER . self::HANDSHAKE . $sessionId);
    }
    
    /**
     * Get a statistic packet
     * 
     * @return Packet
     */
    public function stat($sessionId, $challenge)
    {
        $sessionId = $this->transformLong($sessionId);
        $challenge = $this->transformLong($challenge);
        
        return $this->newPacket(self::HEADER . self::STAT . $sessionId . $challenge);
    }
}
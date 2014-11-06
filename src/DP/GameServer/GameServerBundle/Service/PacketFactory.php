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

namespace DP\GameServer\GameServerBundle\Service;

use DP\GameServer\GameServerBundle\Socket\Packet;

/**
 * @author Albin Kerouanton
 */
class PacketFactory
{    
    /**
     * Transform $var to little endian long value
     *
     * @param long $var
     * @return string
     */
    public static function transformLittleEndianLong($var)
    {
        return pack('V', $var);
    }
    
    /**
     * Transform $var to big endiang long value
     *
     * @param long $var
     * @return string
     */
    public static function transformBigEndianLong($var)
    {
        return pack('N', $var);
    }
    
    /**
     * @see PacketFactory::transformLittleEndianLong()
     */
    public static function transformLong($var)
    {
        return self::transformLittleEndianLong($var);
    }
    
    /**
     * Create easily a packet in subclasses
     * 
     * @param string $content
     * @return \DP\GameServer\GameServerBundle\Socket\Packet 
     */
    public function newPacket($content)
    {
        return new Packet($content);
    }
}

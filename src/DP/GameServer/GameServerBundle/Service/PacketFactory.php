<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

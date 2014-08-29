<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        $challenge = $this->transformBigEndianLong($challenge);
        
        return $this->newPacket(self::HEADER . self::STAT . $sessionId . $challenge);
    }
    
    public function fullStat($sessionId, $challenge)
    {
        $sessionId = $this->transformLong($sessionId);
        $challenge = $this->transformBigEndianLong($challenge);
        
        return $this->newPacket(self::HEADER . self::STAT . $sessionId . $challenge . "\x00\x00\x00\x00");
    }
}
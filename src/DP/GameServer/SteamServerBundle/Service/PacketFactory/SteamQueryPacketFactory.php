<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\SteamServerBundle\Service\PacketFactory;

use DP\GameServer\GameServerBundle\Service\PacketFactory;

/**
 * @author Albin Kerouanton
 */
class SteamQueryPacketFactory extends PacketFactory
{
    const HEADER = "\xFF\xFF\xFF\xFF"; //4 x 0xFF
    const A2A_PING = "\x69"; //0x69
    const A2S_INFO = "TSource Engine Query\0";
    const A2S_PLAYER = "\x55"; //0x55
    const A2S_RULES = "\x56"; //0x56
    
    /**
     * Get a ping request packet
     * 
     * @return \DP\GameServer\GameServerBundle\Socket\Packet
     */
    public function A2A_PING()
    {
//        return $this->newPacket(self::HEADER . self::A2A_PING);
        return $this->A2S_INFO();
    }
    
    /**
     * Get a server infos request packet
     * 
     * @return \DP\GameServer\GameServerBundle\Socket\Packet
     */
    public function A2S_INFO()
    {
        return $this->newPacket(self::HEADER . self::A2S_INFO);
    }
    
    /**
     * Get a players request packet
     * 
     * @param integer $challenge
     * @return \DP\GameServer\GameServerBundle\Socket\Packet
     */
    public function A2S_PLAYER($challenge)
    {
        $challenge = $this->transformLong($challenge);
        
        return $this->newPacket(self::HEADER . self::A2S_PLAYER . $challenge);
    }
    
    /**
     * Get a rules request packet
     * 
     * @return \DP\GameServer\GameServerBundle\Socket\Packet
     */
    public function A2S_RULES($challenge)
    {
        $challenge = $this->transformLong($challenge);
        
        return $this->newPacket(self::HEADER . self::A2S_RULES . $challenge);
    }
    
    /**
     * Get a challenge packet
     * 
     * @return \DP\GameServer\GameServerBundle\Socket\Packet 
     */
    public function A2S_SERVERQUERY_GETCHALLENGE()
    {
        return $this->A2S_PLAYER(-1);
    }
}
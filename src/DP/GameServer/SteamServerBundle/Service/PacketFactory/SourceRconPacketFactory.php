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

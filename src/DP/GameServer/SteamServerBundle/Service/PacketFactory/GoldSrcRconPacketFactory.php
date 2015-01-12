<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\SteamServerBundle\Service\PacketFactory;

use DP\GameServer\GameServerBundle\Service\PacketFactory;

/**
 * @author Albin Kerouanton
 */
class GoldSrcRconPacketFactory extends PacketFactory
{
    const HEADER = "\xFF\xFF\xFF\xFF"; // 0xFF x 4
    const GET_CHALLENGE = 'challenge rcon';
    const EXEC_CMD = 'rcon ';
    
    public function getChallengePacket()
    {
        return $this->newPacket(self::HEADER . self::GET_CHALLENGE);
    }
    
    public function getExecCmdPacket($challenge, $mdp, $cmd)
    {
        return $this->newPacket(self::HEADER . self::EXEC_CMD . ' ' . $challenge . ' "' . $mdp . '" ' . $cmd);
    }
}

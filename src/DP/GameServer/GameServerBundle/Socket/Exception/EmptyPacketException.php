<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\GameServerBundle\Socket\Exception;

/**
 * @author Albin Kerouanton 
 */
class EmptyPacketException extends SocketException
{
    public function __construct()
    {
        parent::__construct('Can\'t get anymore data. The packet is empty.');
    }
}
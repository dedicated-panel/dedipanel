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
class CreateSocketException extends SocketException
{
    public function __construct($type, $sockError){
        parent::__construct('Can\'t create a ' . $type . ' connection.' .
            'Socket error : ' . $sockError);
    }
}
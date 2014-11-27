<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\GameServerBundle\Socket\Exception;

/**
 * @author Albin Kerouanton
 */
class SendDataException extends SocketException
{
    /**
     * @param string $sockError
     */
    public function __construct($sockError)
    {
        parent::__construct('An error has occurred during data uploading.' .
            'Socket error : ' . $sockError);
    }
}
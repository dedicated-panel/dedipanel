<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServerBundle\MinecraftServerBundle\MinecraftQuery\Exception;

class SessionIdNotMatchException extends \Exception
{
    public function __construct()
    {
        parent::__construct('The session id received does not match the id sent.');
    }
}

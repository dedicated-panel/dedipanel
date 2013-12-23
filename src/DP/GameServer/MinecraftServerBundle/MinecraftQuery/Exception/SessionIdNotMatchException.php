<?php

namespace DP\GameServerBundle\MinecraftServerBundle\MinecraftQuery\Exception;

class SessionIdNotMatchException extends \Exception
{
    public function __construct()
    {
        parent::__construct('The session id received does not match the id sent.');
    }
}

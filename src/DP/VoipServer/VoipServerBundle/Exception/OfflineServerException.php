<?php

namespace DP\VoipServer\VoipServerBundle\Exception;

class OfflineServerException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}

<?php

namespace DP\VoipServer\VoipServerBundle\Exception;

class OfflineServerException extends \RuntimeException
{
    /**
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}

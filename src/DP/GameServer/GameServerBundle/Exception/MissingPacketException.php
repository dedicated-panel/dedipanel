<?php

namespace DP\GameServer\GameServerBundle\Exception;

use Dedipanel\PHPSeclibWrapperBundle\Connection\ConnectionInterface;

class MissingPacketException extends \RuntimeException
{
    private $packets;

    public function __construct($conn, $packets)
    {
        if (is_string($packets)) {
            $packets = array($packets);
        }

        $this->message = 'Missing packets ' . implode(', ', $packets) . ' on ' . strval($conn->getServer());
        $this->packets = $packets;
    }

    public function getPackets()
    {
        return $this->packets;
    }
}

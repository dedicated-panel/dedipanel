<?php

namespace DP\Core\CoreBundle\Exception;

use Dedipanel\PHPSeclibWrapperBundle\Connection\ConnectionInterface;

class MissingPacketException extends \RuntimeException
{
    private $packets;

    public function __construct($packets)
    {
        if (is_string($packets)) {
            $packets = array($packets);
        }

        $this->message = 'Missing packets ' . implode(', ', $packets) . '.';
        $this->packets = $packets;
    }

    public function getPackets()
    {
        return $this->packets;
    }
}

<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

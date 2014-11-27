<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\GameServerBundle\Service;

use DP\GameServer\GameServerBundle\Socket\Socket;

/**
 * @author Albin Kerouanton 
 */
class SocketFactory
{
    private $conns;
    private $timeout;
    
    /**
     * Constructor
     * @param array $timeout 1st field for timeout sec, 2nd for timeout usec
     */
    public function __construct(array $timeout)
    {
        $this->timeout = $timeout;
    }
    
    /**
     * Get a socket
     * 
     * @param string $ip
     * @param int $port
     * @param string $type
     * @parram array $callbacks List of callbacks used after recv
     * @return \DP\GameServer\GameServerBundle\Socket\Socket 
     */
    private function getSocket($ip, $port, $type, $callbacks)
    {
        $key = $ip . ':' . $port . '.' . $type;
        
        if (isset($this->conns) && array_key_exists($key, $this->conns)) {
            return $this->conns[$key];
        }
        else {
            $conn = new Socket($ip, $port, $type, $this->timeout, $callbacks);
            $this->conns[$key] = $conn;
            
            return $conn;
        }
    }
    
    /**
     * Get an UDP socket
     * 
     * @param string $ip
     * @param int $port
     * @parram array $callbacks List of callbacks used after recv
     * @return \DP\GameServer\GameServerBundle\Socket\Socket 
     */
    public function getUDPSocket($ip, $port, array $callbacks = null)
    {
        return $this->getSocket($ip, $port, 'udp', $callbacks);
    }
    
    /**
     * Get a TCP socket
     *  
     * @param string $ip
     * @param int $port
     * @parram array $callbacks List of callbacks used after recv
     * @return \DP\GameServer\GameServerBundle\Socket\Socket 
     */
    public function getTCPSocket($ip, $port, $callbacks = null)
    {
        return $this->getSocket($ip, $port, 'tcp', $callbacks);
    }
}

<?php
/*
** Copyright (C) 2010-2012 Kerouanton Albin, Smedts Jérôme
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along
** with this program; if not, write to the Free Software Foundation, Inc.,
** 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
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
    public function getUDPSocket($ip, $port, array $callbacks = array())
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
    public function getTCPSocket($ip, $port, array $callbacks = array())
    {
        return $this->getSocket($ip, $port, 'tcp', $callbacks);
    }
}
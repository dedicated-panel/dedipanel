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

namespace DP\GameServer\GameServerBundle\Socket;

use DP\GameServer\GameServerBundle\Socket\Packet;

use DP\GameServer\GameServerBundle\Socket\Exception\CreateSocketException;
use DP\GameServer\GameServerBundle\Socket\Exception\ConnectionFailedException;
use DP\GameServer\GameServerBundle\Socket\Exception\NotConnectedException;
use DP\GameServer\GameServerBundle\Socket\Exception\SendDataException;
use DP\GameServer\GameServerBundle\Socket\Exception\RecvDataException;
use DP\GameServer\GameServerBundle\Socket\Exception\RecvTimeoutException;

/**
 * @author Albin Kerouanton 
 * 
 * @todo ajout support IPv6
 * @todo ajout callbacks pre/postSend, preRecv & del 1 callback postRecv
 */
class Socket
{
    private $ip;
    private $port;
    private $type;
    private $timeout;
    private $socket;
    private $connected;
    
    private $callbacks;
    
//    const MTU = 1400;
    
    /**
     * Constructor
     * 
     * @param string $ip
     * @param int $port
     * @param string $type (tcp/udp)
     * @param array $timeout (0 => timeout sec, 1 => timeout usec)
     * @param array $callbacks Array of callbacks
     */
    public function __construct(
        $ip, $port, $type, array $timeout, array $callbacks = array())
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->type = $type;
        $this->timeout = $timeout;
        $this->connected = false;
        
        if (is_callable($callbacks['isMultiResp']) && 
            is_callable($callbacks['recvMultiResp'])) {
            $this->callbacks = $callbacks;
        }
    }
    
    /**
     * Connect to the server
     * 
     * @throws CreateSocketException
     * @throws ConnectionFailedException 
     */
    public function connect()
    {
        $domain = AF_INET;
        if ($this->isIPv6()) $domain = AF_INET6;
        
        if ($this->type == 'tcp') {
            $type = SOCK_STREAM;
            $proto = SOL_TCP;
        }
        elseif ($this->type == 'udp') {
            $type = SOCK_DGRAM;
            $proto = SOL_UDP;
        }
        else {
            $type = SOCK_RAW;
            $proto = SOL_ICMP;
        
        }
        
        $this->socket = socket_create($domain, $type, $proto);
        
        if ($this->socket === false) {
            throw new CreateSocketException($type, $this->getLastError());
        }
        
        // On défini la socket comme étant bloquante
        // Et on défini le timeout
        socket_set_block($this->socket);
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => $this->timeout[0], 'usec' => $this->timeout[1]));
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $this->timeout[0], 'usec' => $this->timeout[1]));
                
        $connect = socket_connect($this->socket, $this->ip, $this->port);
        
        if (!$connect) {
            throw new ConnectionFailedException($this->getLastError());
        }
        else {
            $this->connected = true;
        }
    }
    
    /**
     * Disconnect the socket  
     */
    public function disconnect()
    {
        if (!$this->connected) {
            throw new NotConnectedException('The socket is already disconnected.');
        }
        
        $this->connected = false;
        
        socket_close($this->socket);
        unset($this->socket);
    }
    
    /**
     * Send a packet
     * 
     * @param Packet $packet
     * @throws NotConnectedException
     * @throws SendDataException 
     */
    public function send(Packet $packet)
    {
        if (!$this->connected) {
            throw new NotConnectedException('Can\'t send data when the socket is disconnected.');
        }
        
        $len = $packet->getLength();
        $send = socket_send($this->socket, $packet, $len, 0);
        
        if ($send === null || $send != $len) {
            throw new SendDataException($this->getLastError());
        }
    }
    
    /**
     * Receive a packet
     * 
     * @param bool $multiPacket
     * @return \DP\GameServer\GameServerBundle\Socket\Packet
     * @throws NotConnectedException
     * @throws RecvTimeoutException
     * @throws RecvDataException 
     */
    public function recv($multiPacket = true)
    {
        if (!$this->connected) {
            throw new NotConnectedException('Can\'t recv data when the socket is disconnected.');
        }
        
        // On souhaite uniquement lire la socket actuelle
        $read = array($this->socket);
        $write = null;
        $except = null;
        
        // On attend la modif de celle-ci jusqu'au timeout (en sec et usec)
        // socket_select renvoie le nombre de socket modifiés
        // N'ayant passé qu'une socket, si celle-ci est modifié
        // C'est que des données sont arrivés.
        $select = socket_select($read, $write, $except, $this->timeout[0], $this->timeout[1]);
        
        // S'il a bien des données d'arrivés, on les récupères
        // Et on exécute les 2 callbacks de post réception si nécessaire (et si possible)
        // Ceux-ci servant à traiter les cas de réception multi-packets (notamment pour l'UDP)
        if ($select == 1) {
            $packetLength = $this->getSocketBufferSize();
            $read = new Packet(socket_read($this->socket, $packetLength, PHP_BINARY_READ));
            
            if ($multiPacket && is_callable($this->callbacks['isMultiResp'])) {
                if (call_user_func($this->callbacks['isMultiResp'], $read)) {
                    $read = call_user_func($this->callbacks['recvMultiResp'], $read, $this);
                }
            }
            
            return $read;
        }
        elseif ($select === 0) {
            $this->connected = false;
            throw new RecvTimeoutException($this->getLastError());
        }
        else {
            $this->connected = false;
            throw new RecvDataException($this->getLastError());
        }
    }
    
    /**
     * Get socket buffer size
     * @return int 
     */
    public function getSocketBufferSize()
    {
        return socket_get_option($this->socket, SOL_SOCKET, SO_RCVBUF);
    }
    
    /**
     * Get the last error message
     * @return string 
     */
    private function getLastError()
    {
        return socket_strerror(socket_last_error());
    }
    
    /**
     * Set IP
     * 
     * @param string $ip 
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }   
    /**
     * Get IP
     * 
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }
    /**
     * Verify if IP is v6
     * 
     * @todo Add IPv6 support
     * @return bool
     */
    public function isIPv6()
    {
        return false;
    }
    
    /**
     * Set port
     * 
     * @param int $port 
     */
    public function setPort($port)
    {
        $this->port = $port;
    }  
    /**
     * Get port
     * 
     * @return int 
     */
    public function getPort()
    {
        return $this->port;
    }
    
    /**
     * Set type (tcp or udp)
     * 
     * @param int $type 
     */
    public function setType($type)
    {
        $this->type = $type;
    }   
    /**
     * Get type (tcp or udp)
     * 
     * @return int 
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Get socket status (connected or not)
     * 
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }
}
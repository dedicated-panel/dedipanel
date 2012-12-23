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

namespace DP\GameServer\MinecraftServerBundle\MinecraftQuery;

use DP\GameServer\GameServerBundle\Socket\Socket;
use DP\GameServer\GameServerBundle\Socket\Packet;
use DP\GameServer\GameServerBundle\Socket\PacketCollection;
use DP\GameServer\GameServerBundle\Socket\Exception\RecvTimeoutException;
use DP\GameServer\MinecraftServerBundle\MinecraftQuery\Exception;

/**
 * @author Albin Kerouanton 
 */
class MinecraftQuery
{
    private $container;
    private $socket;
    private $packetFactory;

    protected $challenge;
    protected $players;
    protected $serverInfos;
    
    public function __construct($container, $host, $port)
    {
        $this->container = $container;
        $this->packetFactory = $container->get('packet.factory.minecraft.query');
        
        $this->socket = $container->get('socket')->getUDPSocket($host, $port);
        
        try {
            $this->socket->connect();
            $this->getServerInfos();
        }
        catch (ConnectionFailedException $e) {}
        catch (NotConnectedException $e) {}
        catch (Exception\ServerTimeoutException $e) {}
    }
    
    public function getChallenge()
    {
        if (!isset($this->challenge)) {      
            try {
                $sessionId = rand();
                $this->socket->send($this->packetFactory->handshake($sessionId));
            
                $resp = $this->socket->recv();
                $data = $resp->extract(array(
                    'type' => 'byte', 
                    'sessionId' => 'long', 
                    'challenge' => 'string'
                ));
                
                if ($data['sessionId'] == $sessionId) {
                    $this->challenge = $data['challenge'];
                }
            }
            catch (RecvTimeoutException $e) {
                throw new Exception\ServerTimeoutException;
            }
        }
        
        return $this->challenge;
    }
    
    public function getServerInfos()
    {
        if (!isset($this->serverInfos)) {
            try {
                $sessionId = rand();
                $this->socket->send($this->packetFactory->stat($sessionId, $this->getChallenge()));
                
                $resp = $this->socket->recv();
                $data = $resp->extract(array(
                    'type' => 'byte', 
                    'sessionId' => 'long', 
                    'motd' => 'string', 
                    'gametype' => 'string', 
                    'map' => 'string', 
                    'numplayers' => 'string', 
                    'maxplayers' => 'string', 
                    'hostport' => 'short', 
                    'hostip' => 'string'
                ));
                
                unset($data['type'], $data['sessionId']);
                $this->serverInfos = $data;
            }
            catch (RecvTimeoutException $e) {
                throw new Exception\ServerTimeoutException;
            }
        }
        
        return $this->serverInfos;
    }
}
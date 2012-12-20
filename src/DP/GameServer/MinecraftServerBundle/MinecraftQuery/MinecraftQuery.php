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
    
    public function __construct($container, $host, $port)
    {
        $this->container = $container;
        $this->packetFactory = $container->get('packet.factory.minecraft.query');
        
        $this->socket = $container->get('socket')->getTCPSocket($host, $port);
        
        try {
            $this->socket->connect();
            $this->retrieveChallenge();
        }
        catch (ConnectionFailedException $e) {}
        catch (NotConnectedException $e) {}
    }
    
    public function retrieveChallenge()
    {
        $sessionId = rand();
        $this->socket->send($this->packetFactory->handshake($sessionId));
        $resp = $this->socket->recv();
        var_dump($resp);
    }
}
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

namespace DP\GameServer\SteamServerBundle\SteamQuery;

use DP\GameServer\GameServerBundle\Socket\Socket;
use DP\GameServer\GameServerBundle\Socket\Packet;
use DP\GameServer\GameServerBundle\Socket\PacketCollection;

use DP\GameServer\GameServerBundle\Socket\Exception\ConnectionFailedException;
use DP\GameServer\GameServerBundle\Socket\Exception\NotConnectedException;
use DP\GameServer\GameServerBundle\Socket\Exception\RecvTimeoutException;
use DP\GameServer\SteamServerBundle\SteamQuery\Exception\ServerTimeoutException;

class SourceRcon
{
    private $socket;
    private $packetFactory;
    private $rconPassword;
    private $authenticated = null;
    
    public function __construct($container, $host, $port, $rconPassword)
    {
        $callback = function(Packet $packet, Socket $socket) {
            if (is_null($packet) || $packet->isEmpty()) return false;
            
            $remaining = $packet->getLong(false);
            $packet->setPos(4);
            $id = $packet->getLong(false);
            
            if ($remaining > 0) {
                $splittedPackets = new PacketCollection();
                $respId = null;
                
                do {
                    if (!$respId) {
                        $respId = $id;
                    }
                    elseif ($respId != $id) {
                        $packet = $socket->recv(false);
                        continue;
                    }
                    
                    $splittedPackets->add($packet->rewind());
                    $packet = $socket->recv(false, $remaining);
                    $remaining -= $packet->getLength();
                } while ($remaining > 0);
                
                return $splittedPackets->reassemble(
                    function(Packet $bigPacket, Packet $packet) {
                        if ($bigPacket->isEmpty()) {
                            $bigPacket->addContent($packet->getContent());
                            $bigPacket->setPos($packet->getLength()-2);
                        }
                        else {
                            $bigPacket->addContent($packet);
                        }
                        
                        return $bigPacket;
                    }
                )->rewind();
            }
            else {
                return $packet;
            }
        };
        
        $this->rconPassword = $rconPassword;
        $this->socket = $container->get('socket')->getTCPSocket($host, $port, $callback);
        $this->packetFactory = $container->get('packet.factory.steam.rcon.source');
        
        try {
            $this->socket->connect();
            $this->auth();
        }
        catch (ConnectionFailedException $e) {}
    }
    
    private function auth()
    {
        if ($this->authenticated == null) {
            $id = null;
            $packet = $this->packetFactory->getAuthPacket($id, $this->rconPassword);
            $this->socket->send($packet);
            $resp = $this->socket->recv(false);
            
            if ($resp->isEmpty()) {
                $this->authenticated = false;
                return;
            }
            
            $infos = $resp->extract(array(
                'size' => 'long', 
                'id' => 'long', 
                'type' => 'long', 
                's1' => 'string', 
                's2' => 'string'
            ));
            
            if ($infos['type'] == $this->packetFactory->SERVER_RESPONSE_VALUE) {
                $resp = $this->socket->recv(false);
                $infos = $resp->extract(array(
                    'size' => 'long', 
                    'id' => 'long', 
                    'type' => 'long', 
                    's1' => 'string', 
                    's2' => 'string'
                ));
            }
            
            if ($infos['id'] == $id && 
                $infos['type'] == $this->packetFactory->SERVER_AUTH_RESPONSE) {
                $this->authenticated = true;
            }
            else {
                $this->authenticated = false;
            }
        }
    }
    
    public function sendCmd($cmd)
    {
        if ($this->authenticated) {
            $id = null;
            $packet = $this->packetFactory->getCmdPacket($id, $cmd);
            $this->socket->send($packet);
            
            $resp = $this->recv();
            
            if ($resp == null) {
                return false;
            }
            
            if ($resp->setPos(8)->getLong(false) != $this->packetFactory->SERVER_RESPONSE_VALUE 
                || $resp->setPos(4)->getLong(false) != $id) {
                return false;
            }
            
            return $resp->rewind();
        }
        else {
            return false;
        }
    }
    
    /**
     * Get mulitple packets from socket recv method
     * Return reassemble packets if there is reponse(s)
     * Or return null if there is a RecvTimeoutException catched 
     * before any content has been received.
     * 
     * @param type $multipacket
     * @return \DP\GameServer\GameServerBundle\Socket\Packet|null
     */
    private function recv($multipacket = true)
    {
        $packets = new PacketCollection();
        
        do {
            try {
                $resp = $this->socket->recv();
                $packets->add($resp->rewind());
            }
            catch (RecvTimeoutException $e) {
                $resp = null;
            }
            
        } while ($resp != null);
        
        if ($resp == null && $packets->count() == 0) {
            return null;
        }
        else {
            return $packets->reassemble();
        }
        
        return $packet->rewind();
    }
}

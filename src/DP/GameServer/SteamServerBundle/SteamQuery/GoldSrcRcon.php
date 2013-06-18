<?php

/*
** Copyright (C) 2010-2013 Kerouanton Albin, Smedts Jérôme
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

use DP\GameServer\GameServerBundle\Socket\Exception\ConnectionFailedException;

class GoldSrcRcon
{
    protected $ip;
    protected $port;
    protected $password;
    
    protected $socket;
    protected $packetFactory;
    protected $challenge;
    
    public function __construct($container, $ip, $port, $password)
    {        
        $this->password = $password;
        $this->socket = $container->get('socket')->getUDPSocket($ip, $port);
        $this->packetFactory = $container->get('packet.factory.rcon.goldsrc');
        try {
            $this->socket->connect();
            $this->getChallenge();
        }
        catch (ConnectionFailedException $e) {}
    }
    
    public function getChallenge()
    {
        if (!isset($this->challenge)) {
            $packet = $this->packetFactory->getChallengePacket();
            $this->socket->send($packet);
            
            $resp = $this->socket->recv()->getString();
            $resp = explode(' ', $resp);
            
            $challenge = substr($resp[2], 0, -1);
            $this->challenge = $challenge;
        }
        
        return $this->challenge;
    }
    
    public function sendCmd($cmd)
    {
        $challenge = $this->getChallenge();
        $packet = $this->packetFactory->getExecCmdPacket($challenge, $this->password, $cmd);
        $this->socket->send($packet);
        
        $resp = $this->socket->recv()->extract(array(
            'unknown'   => 'byte', 
            'resp'      => 'string', 
        ));
        
        return $resp['resp'];
    }
}
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

use DP\Core\CoreBundle\Socket\Exception\ConnectionFailedException;
use DP\GameServer\GameServerBundle\Query\RconInterface;

class GoldSrcRcon implements RconInterface
{
    protected $ip;
    protected $port;
    protected $password;
    
    protected $socket;
    protected $packetFactory;
    protected $challenge;
    
    protected $fullyConstructed = false;
    
    public function __construct($container, $ip, $port, $password)
    {        
        $this->password = $password;
        $this->socket = $container->get('socket')->getUDPSocket($ip, $port);
        $this->packetFactory = $container->get('packet.factory.rcon.goldsrc');
    }
    
    /**
     * Permet de ne finaliser la création du rcon qu'en cas d'utilisation de celui-ci
     * Permet ainsi à la classe d'être instancié un certains nombre de fois sans pour autant être utilisé 
     * (utile pour le QueryInjector)
     */
    protected function fullConstruct()
    {
        try {
            $this->socket->connect();
            $this->getChallenge();
        }
        catch (ConnectionFailedException $e) {}
        
        $this->fullyConstructed = true;
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
        if (!$this->fullyConstructed) {
            $this->fullConstruct();
        }
        
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
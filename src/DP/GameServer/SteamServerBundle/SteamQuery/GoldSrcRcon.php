<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\SteamServerBundle\SteamQuery;

use DP\GameServer\GameServerBundle\Socket\Exception\ConnectionFailedException;
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
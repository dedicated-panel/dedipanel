<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\MinecraftServerBundle\MinecraftQuery;

use DP\GameServer\GameServerBundle\Query\QueryInterface;
use DP\GameServer\GameServerBundle\Socket\Socket;
use DP\GameServer\GameServerBundle\Socket\Packet;
use DP\GameServer\GameServerBundle\Socket\PacketCollection;
use DP\GameServer\GameServerBundle\Socket\Exception\RecvTimeoutException;
use DP\GameServer\MinecraftServerBundle\MinecraftQuery\Exception;

/**
 * @author Albin Kerouanton 
 */
class MinecraftQuery implements QueryInterface
{
    private $container;
    private $socket;
    private $packetFactory;

    protected $challenge;
    protected $players;
    protected $serverInfos;
    protected $online;
    protected $plugins;
    
    /**
     * @param string $host
     * @param integer $port
     */
    public function __construct($container, $host, $port)
    {
        $this->container = $container;
        $this->packetFactory = $container->get('packet.factory.minecraft.query');
        
        $this->socket = $container->get('socket')->getUDPSocket($host, $port);
        
        try {
            $this->socket->connect();
            // On récupère le challenge pour s'assurer que le serveur est bien en ligne
            $this->getChallenge();
            
            $this->online = true;
        }
        catch (Exception\ServerTimeoutException $e) {
            $this->online = false;
        }
    }
    
    public function getChallenge()
    {
        if (!isset($this->challenge)) {      
            try {
                $sessionId = $this->generateSessionId();
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
                else {
                    throw new Exception\SessionIdNotMatchException;
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
                $sessionId = $this->generateSessionId();
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
    
    public function getPlayers()
    {
        if (!isset($this->players)) {
            $sessionId = $this->generateSessionId();
            $this->socket->send($this->packetFactory->fullStat($sessionId, $this->getChallenge()));
            
            // 1 octet + 1 int + string "splitnum\x00\x80\x00" (2 + 4 + 11 = 16)
            $resp = $this->socket->recv()->setPos(16);
            
            do {
                $varname = $resp->getString();
                
                if (!empty($varname)) {
                    $val = $resp->getString();
                    
                    if ($varname == 'plugins') {
                        $this->plugins = $val;
                    }
                }
            } while ($varname != '');
            
            $playerPartPos = strpos($resp->rewind()->getContent(), "player_\x00\x00") + strlen("player_\x00\x00");
            $resp->setPos($playerPartPos);
            
            while ($playerName = $resp->getString()) {
                $this->players[] = $playerName;
            }
        }
        
        return $this->players;
    }
    
    public function verifyStatus()
    {
        return true;
    }
    
    public function isOnline()
    {
        return $this->online;
    }
    
    public function isBanned()
    {
        return false;
    }
    
    public function getActivePlugins()
    {
        if (!isset($this->plugins)) {
            $this->getPlayers();
        }
        
        return $this->plugins;
    }
    
    private function generateSessionId()
    {
        return rand(0, 100) & 0x0F0F0F0F;
    }
}

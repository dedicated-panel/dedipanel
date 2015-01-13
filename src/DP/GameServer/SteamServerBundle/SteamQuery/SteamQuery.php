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

use DP\GameServer\GameServerBundle\Socket\Socket;
use DP\GameServer\GameServerBundle\Socket\Packet;
use DP\GameServer\GameServerBundle\Socket\PacketCollection;
use DP\GameServer\GameServerBundle\Query\QueryInterface;
use DP\GameServer\GameServerBundle\Socket\Exception\ConnectionFailedException;
use DP\GameServer\GameServerBundle\Socket\Exception\NotConnectedException;
use DP\GameServer\GameServerBundle\Socket\Exception\RecvTimeoutException;
use DP\GameServer\SteamServerBundle\SteamQuery\Exception\ServerTimeoutException;
use DP\Core\CoreBundle\Exception\IPBannedException;
use DP\GameServer\SteamServerBundle\SteamQuery\Exception\UnexpectedServerTypeException;

/**
 * @author Albin Kerouanton 
 */
class SteamQuery implements QueryInterface
{
    private $container;
    private $socket;
    private $packetFactory;

    protected $challenge;
    protected $latency;
    protected $serverInfos;
    protected $players;
    protected $rules;
    protected $banned = null;
    protected $type;
    
    const TYPE_GOLDSRC = 1;
    const TYPE_SOURCE  = 2;
    const TYPE_HLTV    = 3;
    
    /**
     * Constructor
     * 
     * @param Service Container $container
     * @param string $host
     * @param int $port
     */
    public function __construct($container, $host, $port, $type)
    {
        $this->type = $type;
        
        // On ne déclare pas les 2 callbacks simultanément
        // Puisque le 2nd fait appel au 1er
        $callbacks = array(
            Socket::MULTI_DETECTOR => function ($packet) {
                if (is_null($packet)) return false;

                $val = $packet->getLong();
                return $val == -2;
            }
        ); 
        $callbacks[Socket::MULTI_RECEIVER] = function(Packet $packet, Socket $socket) use ($callbacks, $type) {
            $splittedPackets = new PacketCollection();
            $respId = null;

            do {                
                // On récupère l'id de la transmission
                // Et on vérifie que les packets récupérés aient le même ID
                $id = $packet->getLong();
                if (!$respId) {
                    $respId = $id;
                }
                elseif ($respId != $id) {
                    $packet = null;
                    $packet = $socket->recv(false);
                    continue;
                }

                if ($type == SteamQuery::TYPE_GOLDSRC || $type == SteamQuery::TYPE_HLTV) {
                    $infosPacket = $packet->getByte();
                    $nbrePacket = $infosPacket & 0xF;
                    $packetId = $infosPacket >> 4;
                }
                else {
                    $nbrePacket = $packet->getByte();
                    $packetId = $packet->getByte();
                }

                $splittedPackets[$packetId] = $packet;

                // On remet à zéro le packet pour ne pas avoir de boucle infinie
                $packet = null;
                $isMultiResp = false;
                if (count($splittedPackets) < $nbrePacket) {
                    $packet = $socket->recv(false);
                    $isMultiResp = call_user_func($callbacks[Socket::MULTI_DETECTOR], $packet);
                }
            } while (!empty($packet) && $isMultiResp == true);

            // Les réponses multi packet une fois réassemblé
            // Comence par l'entier -1
            $ret = $splittedPackets->reassemble();
            $ret->getLong();
            
            return $ret;
        };
        
        $this->container = $container;
        $this->packetFactory = $container->get('packet.factory.steam.query');
        
        $this->socket = $container->get('socket')->getUDPSocket($host, $port, $callbacks);
        
        try {
            $this->socket->connect();
            $this->getLatency();
            $this->isBanned();
            $this->getChallenge();
        }
        catch (ConnectionFailedException $e) {}
        catch (IPBannedException $e) {}
        catch (ServerTimeoutException $e) {}
        catch (NotConnectedException $e) {}
    }
    
    public function verifyStatus()
    {
        $infos = $this->getServerInfos();
        
        if (empty($infos) || ($this->type == SteamQuery::TYPE_HLTV && $infos['protocol'] != 0) 
        ||  ($this->type != SteamQuery::TYPE_HLTV && $infos['protocol'] == 0)) {
            $this->latency = false;
            throw new UnexpectedServerTypeException($this->type == SteamQuery::TYPE_HLTV);
        }
        
        return true;
    }
    
    /**
     * Get the server info
     * 
     * @return array|bool
     * @throws Exception\ServerTimeoutException 
     */
    public function getServerInfos()
    {
        if ($this->banned || $this->latency === false) {
            return false;
        }
        
        if (!isset($this->serverInfos)) {
            try {
                $this->socket->send($this->packetFactory->A2S_INFO());
                $resp = $this->socket->recv();
                
                $infos = $resp->rewind()->extract(array(
                    'header' => 'byte',
                    'protocol' => 'byte', 
                    'serverName' => 'string',
                    'map' => 'string',
                    'gameDir' => 'string',
                    'gameName' => 'string',
                    'appId' => 'short',
                    'players' => 'byte',
                    'maxPlayers' => 'byte',
                    'bot' => 'byte',
                    'serverType' => 'byte',
                    'os' => 'byte',
                    'password' => 'byte',
                    'vac' => 'byte',
                    'gameVer' => 'string',
                    'edf' => 'byte'));

                if ($infos['edf'] & 0x080) {
                    $infos['port'] = $resp->getShort();
                }
                elseif ($infos['edf'] & 0x040) {
                    $infos['hltv_port'] = $resp->getShort();
                    $infos['hltv_name'] = $resp->getString();
                }
                elseif ($infos['edf'] & 0x020) {
                    $infos['keywords'] = $resp->getString();
                }
                
                $this->serverInfos = $infos;
            }
            catch (RecvTimeoutException $e) {
                throw new Exception\ServerTimeoutException();
            }
            catch (NotConnectedException $e) {
                $this->serverInfos = array();
            }
        }
        
        return $this->serverInfos;
    }
    
    /**
     * Get server challenge
     * 
     * @return long
     */
    protected function getChallenge()
    {
        if (!isset($this->challenge)) {
            try {
                $packet = $this->packetFactory->A2S_SERVERQUERY_GETCHALLENGE();
                $this->socket->send($packet);
                $resp = $this->socket->recv();

                $data = $resp->extract(array(
                    'header' => 'byte', 
                    'challenge' => 'long', 
                ));

                if ($data['header'] == 65) {
                    $this->challenge = $data['challenge'];
                }
            }
            catch (RecvTimeoutException $e) {
                throw new ServerTimeoutException();
            }
        }
        
        return $this->challenge;
    }
    
    /**
     * Get players list
     * 
     * @return array
     * @throws ServerTimeoutException 
     */
    public function getPlayers()
    {
        if (!isset($this->players)) {
            try {
                $challenge = $this->getChallenge();
                
                $this->socket->send($this->packetFactory->A2S_PLAYER($challenge));
                $resp = $this->socket->recv();

                $players = array();
                $header = $resp->extract(
                    array('header' => 'byte', 'nb_players' => 'byte'));

                for ($i = 0, $max = $header['nb_players']; $i < $max; ++$i) {
                    $players[$i] = $resp->extract(array('id' => 'byte', 
                        'nom' => 'string', 'score' => 'long', 'timeConnected' => 'float'));
                }

                $this->players = $players;
            }
            catch (RecvTimeoutException $e) {
                throw new ServerTimeoutException();
            }
            catch (NotConnectedException $e) {
                $this->players = array();
            }
        }
        
        return $this->players;
    }
    
    /**
     * Get rules list
     * @return type
     * @throws ServerTimeoutException 
     */
    public function getRules()
    {
        if (!isset($this->rules)) {
            try {
                $this->socket->send($this->packetFactory->A2S_RULES($this->getChallenge()));
                $resp = $this->socket->recv();
                
                $rules = array();
                $header = $resp->extract(
                    array('header' => 'byte', 'nb_rules' => 'short')
                );

                if ($header['header'] == 69) {
                    for ($i = 0, $max = $header['nb_rules']; $i < $max; ++$i) {
                        $rules[$i] = $resp->extract(
                            array('name' => 'string', 'value' => 'string')
                        );
                    }

                    $this->rules = $rules;
                }
            }
            catch (RecvTimeoutException $e) {
                throw new ServerTimeoutException();
            }
            catch (NotConnectedException $e) {
                $this->rules = array();
            }
        }
        
        return $this->rules;
    }
    
    /**
     * Get the server latency
     * @return float
     */
    public function getLatency()
    {
        if (!isset($this->latency)) {
            $packet = $this->packetFactory->A2A_PING();
            
            try {
                $ping = microtime(true);
                $this->socket->send($packet);
                $this->socket->recv();
                $this->latency = round((microtime(true) - $ping) * 1000);
            }
            catch (RecvTimeoutException $e) {
                $this->latency = false;
            }
            catch (NotConnectedException $e) {
                $this->latency = false;
            }
        }
        
        return $this->latency;
    }
    
    /**
     * Check if the server is online
     * @return bool 
     */
    public function isOnline()
    {
        return ($this->getLatency() != false);
    }
    
    /**
     * Alias of isOnline method
     */
    public function getIsOnline()
    {
        return $this->isOnline();
    }
    
    /**
     * Check if the IP is banned from the server
     * This method is executed before the serverInfos
     * @return bool
     */
    public function isBanned($fromTpl = false)
    {
        if ($this->banned === null && $this->latency === null) {
            try {
                $this->socket->send($this->packetFactory->A2A_PING());
                $resp = $this->socket->recv();
                
                if (strpos($resp->setPos(5)->getString(false), 
                    'Banned by server') !== false) {
                    $this->latency = false;
                    $this->banned = true;
                    
                    if ($fromTpl !== true) {
                     throw new IPBannedException();   
                    }
                }
                $this->banned = false;
            }
            catch (RecvTimeoutException $e) {
                $this->latency = false;
                $this->serverInfos = array();
                throw new ServerTimeoutException();
            }
            catch (NotConnectedException $e) {
                $this->latency = false;
                $this->serverInfos = array();
                throw new ServerTimeoutException();
            }
        }
        
        return $this->banned;
    }
}

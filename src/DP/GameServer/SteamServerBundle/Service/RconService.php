<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\SteamServerBundle\Service;

use DP\GameServer\SteamServerBundle\SteamQuery\SteamQuery;
use DP\GameServer\SteamServerBundle\SteamQuery\GoldSrcRcon;
use DP\GameServer\SteamServerBundle\SteamQuery\SourceRcon;

class RconService
{
    private $container;
    private $rcon;
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function getRcon($ip, $port, $pass, $type)
    {
        $key = $ip . ':' . $port;
        
        if (!isset($this->rcon) || !array_key_exists($key, $this->rcon)) {            
            $this->rcon[$key] = $this->instanciateRcon($ip, $port, $pass, $type);
        }
        
        return $this->rcon[$key];
    }
    
    protected function instanciateRcon($ip, $port, $pass, $type)
    {
        if ($type == SteamQuery::TYPE_GOLDSRC) {
            return new GoldSrcRcon($this->container, $ip, $port, $pass);
        }
        elseif ($type == SteamQuery::TYPE_SOURCE) {
            return new SourceRcon($this->container, $ip, $port, $pass);
        }
    }
}

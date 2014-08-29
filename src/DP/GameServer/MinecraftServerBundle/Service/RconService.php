<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\MinecraftServerBundle\Service;

use DP\GameServer\MinecraftServerBundle\MinecraftQuery\MinecraftRcon;

class RconService
{
    private $container;
    private $rcon;
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function getRcon($ip, $port, $pass)
    {
        $key = $ip . ':' . $port;
        
        if (!isset($this->rcon) || !array_key_exists($key, $this->rcon)) {            
            $this->rcon[$key] = new MinecraftRcon($this->container, $ip, $port, $pass);
        }
        
        return $this->rcon[$key];
    }
}

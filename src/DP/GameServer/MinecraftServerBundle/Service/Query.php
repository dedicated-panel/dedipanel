<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\MinecraftServerBundle\Service;

use DP\GameServer\MinecraftServerBundle\MinecraftQuery\MinecraftQuery;

/**
 * Query Service
 * @author Albin Kerouanton
 */
class Query
{
    private $container;
    private $queries;
    
    /**
     * Constructor
     * @param Service Container $container 
     */
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    /**
     * Get Server Query
     * 
     * @param string $ip
     * @param int $port
     * @return \DP\GameServer\MinecraftServerBundle\MinecraftQuery\MinecraftQuery
     */
    public function getServerQuery($ip, $port)
    {
        $key = $ip . ':' . $port;
        
        if (!isset($this->queries) || !array_key_exists($key, $this->queries)) {
            $this->queries[$key] = new MinecraftQuery($this->container, $ip, $port);
        }
        
        return $this->queries[$key];
    }
}

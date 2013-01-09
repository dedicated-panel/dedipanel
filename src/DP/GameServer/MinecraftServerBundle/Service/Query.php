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
     * @return \DP\GameServer\SteamServerBundle\SteamQuery\SteamQuery
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

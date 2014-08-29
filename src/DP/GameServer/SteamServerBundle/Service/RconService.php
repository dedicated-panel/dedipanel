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

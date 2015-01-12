<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

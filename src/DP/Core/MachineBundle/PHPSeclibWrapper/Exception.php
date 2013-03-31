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

namespace PHPSeclibWrapper\Exception;

use DP\Core\MachineBundle\PHPSeclibWrapper\PHPSeclibWrapper;

abstract class BaseException extends \Exception {}

class FileNotFoundException extends BaseException
{
    public function __construct($message, PHPSeclibWrapper $srv)
    {
        parent::__construct('The private key file for ' . $srv->getUser() . '@' .
            $srv->getHost() . ' ' . $message);
    }  
}
class EmptyKeyfileException extends BaseException
{
    public function __construct(PHPSeclibWrapper $srv)
    {
        parent::__construct('The private key file for ' . $srv->getUser() . '@' .
             $srv->getHost() . ':' . $srv->getPort() . ' is empty.');
    }
}

class ConnectionErrorException extends BaseException
{
    public function __construct(PHPSeclibWrapper $srv)
    {
        parent::__construct('Connection to ' . $srv->getUser() . '@' . 
            $srv->getHost() .':' . $srv->getPort() . ' failed.');
    }
}

class IncompleteLoginIDException extends BaseException
{
    public function __construct(PHPSeclibWrapper $srv)
    {
        parent::__construct('Incomplete login IDs for ' . $srv->getUser() . 
            '@' . $srv->getHost() . ':' . $srv->getPort());
    }
}

class MissingPacketException extends BaseException
{
    public function __construct(PHPSeclibWrapper $srv, $packet)
    {
        if (is_array($packet) == true) {
            $packet = implode(', ', $packet);
            
            parent::__construct('Some packets (' . $packet . ') are missing on your system (' . $srv->getUser() . '@' . 
            $srv->getHost() .':' . $srv->getPort() . ').');
        }
        else {
            parent::__construct('A packet (' . $packet . ') is missing on your system (' . $srv->getUser() . '@' . 
            $srv->getHost() .':' . $srv->getPort() . ').');
        }
    }
}


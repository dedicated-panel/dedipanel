<?php
/*
** Copyright (C) 2010-2011 Kerouanton Albin, Smedts Jérôme
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

define('DIR_QUERY', dirname(__FILE__));

require_once(QUERY_DIR . 'Rcon.class.php');
require_once(QUERY_DIR . 'Query.class.php');

class Server {
    public static function getServer($type = self::STYPE_GOLDSRC, $ip = '127.0.0.1', $port = 27015) {
        $cle = $ip . ':' . $port;

        // Si aucune serveur correspondant à l'ip et au port n'est présent
        // On le créer
        if (isset(self::$servers) && array_key_exists($cle, self::$servers)) {
            $serv = self::$servers[$cle];
        }
        else {
            $serv = new self($ip, $port, $type);
        }

        return $serv;
    }
    public static function getTypeServ($bin) {
        return ($bin == 'srcds_run') ? self::STYPE_SOURCE : self::STYPE_GOLDSRC;
    }

    public function __construct($ip = '127.0.0.1', $port = 27015, $type = self::STYPE_GOLDSRC) {
        $this->ip = $ip;
        $this->port = $port;
        $this->type = $type;

        if (isset($pw)) $this->rconpw = $pw;

        $cle = $ip . ':' . $port;
        self::$servers[$cle] = $this;
    }

    public function setRconPasswrd($pw) {
        $this->rconpw = $pw;
    }

    public function getQuery() {
        if (!isset($this->query)) {
            if ($this->type == self::STYPE_GOLDSRC) {
                $this->query = new GoldSrcQuery($this->ip, $this->port);
            }
            else {
                $this->query = new SourceQuery($this->ip, $this->port);
            }
        }

        return $this->query;
    }
    public function getRcon($pw = null) {
        if (!isset($this->rcon)) {
            // On récupère le mdp rcon s'il n'a pas déjà été fourniif
            if (!isset($this->rconpw)) {
                if (isset($pw)) {
                    $this->rconpw = $pw;
                }
                else {
                    trigger_error('Server::getRcon(): Le mdp rcon n\'a pas été précisé.');
                    return false;
                }
            }

            if ($this->type == self::STYPE_GOLDSRC) {
                $this->rcon = new GoldSrcRcon($this->ip, $this->port, $this->rconpw);
            }
            else {
                $this->rcon = new SourceRcon($this->ip, $this->port, $this->rconpw);
            }
        }

        return $this->rcon;
    }
    
    // On empêche la copie
    private function __clone() {}

    private $ip;
    private $port;
    private $type;
    private $rconpw;

    private $query;
    private $rcon;

    // Contient tous les serveurs instancié via cette classe
    private static $servers;

    const ADD_SERVER = 0;
    const STYPE_GOLDSRC = 1;
    const STYPE_SOURCE = 2;
}
?>
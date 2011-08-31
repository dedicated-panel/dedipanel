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

require_once(DIR_QUERY . '/Packet.class.php');

function error_handler($no, $str, $file, $lne) {
    if ($no == E_USER_ERROR) {
        echo '<p><strong>Erreur fatale :</strong> ' , $str , '</p>';
    }
    elseif ($no == E_USER_NOTICE) {
        echo '<p><strong>Avertissement :</strong> ' , $str , '</p>';
    }
}
//set_error_handler('error_handler');

abstract class Socket extends Packet {
    public function __construct($ip, $port, $ip6 = false) {
        $this->ip = $ip;
        $this->port = $port;
        $this->ip6 = $ip6;

        // On créer la socket et on se connecte
        $this->create();
        $this->connect();

        // On défini la socket comme étant bloquante
        socket_set_block($this->socket);
    }

    protected function lastError() {
        return socket_strerror(socket_last_error());
    }

    abstract protected function create();
    private function connect() {
        $this->connected = socket_connect($this->socket, $this->ip, $this->port);

        if (!$this->connected) {
            trigger_error('Socket::connect() connection error: ' . $this->lastError(), E_USER_ERROR);
        }
    }
    public function disconnect() {
        socket_close($this->socket);

        $this->connected = false;
        unset($this->socket);
    }

    protected function send($packet, $len) {
        $send = socket_send($this->socket, $packet, $len, 0);

        if ($send != $len) {
            trigger_error('Socket::send len error: ' . $this->lastError());
        }

        if ($send === false) {
            trigger_error('Socket::send error: ' . $this->lastError());
        }
    }
    // Cette méthode permet de recevoir les données multi packets founri par le serveur
    // Elle utilise la méthode abstraite recvMultiPackets qui est défini dans les classes filles
    // Chacune utilisant une implémentation différentes dû aux différences des headers Source et GoldSrc
    protected function recv($multipacket = true, $length = MTU) {
        $packet = parent::recv($length);

        if (!$packet) return false;

        if (!$multipacket) {
            return $packet;
        }
        elseif ($this->isMultiPacketResp($packet, $length)) {
            return $this->recvMultiPackets($packet, $length);
        }
        else {
            return array($packet);
        }
    }
    abstract protected function recvMultiPackets($packet, $length);
    
    public function getIp() {
        return $this->ip;
    }
    public function getPort() {
        return $this->port;
    }

    public function isConnected() { return $this->connected; }

    protected $ip;
    protected $port;
    protected $ip6;
    protected $socket;
    protected $connected;
}

abstract class SocketUDP extends Socket {
    protected function create() {
        if ($this->ip6 === false) {
            $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        }
        else {
            $this->socket = socket_create(AF_INET6, SOCK_DGRAM, SOL_UDP);
        }

        if ($this->socket === false) {
            trigger_error('SocketUDP::create init error: ' . $this->lastError(), E_USER_ERROR);
        }
    }
}

abstract class SocketTCP extends Socket {
    protected function create() {
        if ($this->ip6 === false) {
            $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        }
        else {
            $this->socket = socket_create(AF_INET6, SOCK_STREAM, SOL_TCP);
        }

        if ($this->socket === false) {
            trigger_error('SocketTCP::create() init error: ' . $this->lastError(), E_USER_ERROR);
        }
    }
}
?>
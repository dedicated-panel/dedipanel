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

define('TIMEOUT_SEC', 1);
define('TIMEOUT_USEC', 0);

abstract class Packet {
    protected function recv($length) {
        if ($this->connected === false) {
            return;
            trigger_error('Packet::recv call when socket is disconnected.');
        }

        $read = array($this->socket);
        $write = array();
        $except = array();

        $recv = socket_select($read, $write, $except, TIMEOUT_SEC, TIMEOUT_USEC);
        
        if ($recv === false) {
            trigger_error('Packet::recv error: ' . $this->lastError());
            return false;
        }
        elseif ($recv == 1) {
            $read = @socket_read($this->socket, $length, PHP_BINARY_READ);
            return $read;
        }
        elseif ($recv == 0 && socket_last_error() == 0) {
            // Déclenche un notice inutile
            // trigger_error('Packet::recv timeout: ' . $this->lastError());
            return null;
        }
    }
	
    public static function getByte(&$str) {
        if (empty($str))
            return null;

        $data = substr($str, 0, 1);
        $str = substr($str, 1);
        $data = unpack('cvalue', $data);

        return $data['value'];
    }
    public static function getShort(&$str) {
        if (empty($str))
            return null;

        $data = substr($str, 0, 2);
        $data = unpack('svalue', $data);

        $str = substr($str, 2);

        return $data['value'];
    }
    public static function getLong(&$str) {
        if (empty($str))
            return null;

        $data = substr($str, 0, 4);
        $data = unpack('lvalue', $data);

        $str = substr($str, 4);

        return $data['value'];
    }
    public static function getInt(&$str) {
        if (empty($str))
            return null;

        $data = substr($str, 0, 4);
        $data = unpack('ivalue', $data);

        $str = substr($str, 4);

        return $data['value'];
    }
    public static function getFloat(&$str) {
        if (empty($str))
            return null;

        $data = substr($str, 0, 4);
        $data = unpack('fvalue', $data);

        $str = substr($str, 4);

        return $data['value'];
    }
    public static function getString(&$str) {
        return Packet::getStr($str);
    }
    public function getStr(&$str) {
        if (empty($str)) return null;

        // On récupère la première chaîne dasn $str2 (qu'on va renvoyé)
        // Et modifie $str pour qu'elle ne contienne plus cette portion
        $str2 = strstr($str, "\0", true);

        $pos = strpos($str, "\0");
        $str = substr($str, $pos+1);

        return $str2;
    }
    public static function extractPacket(&$packet, $vars) {
        $ret = array();

        foreach ($vars AS $var_name => $var_type) {
            $extract = 'get' . ucfirst($var_type);
            $ret[$var_name] = self::$extract($packet);
        }

        return $ret;
    }

    public static function Long($var) {
        $pack = pack('V', $var);
        return $pack;
    }

    public static function addLong(&$str, $var) {
        $data = $str;
        $data .= pack('V', $var);

        $str = $data;
    }
}
?>
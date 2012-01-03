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

namespace DP\GameServer\GameServerBundle\Socket;

use DP\GameServer\GameServerBundle\Socket\Exception\EmptyPacketException;

/**
 * @author Albin Kerouanton 
 */
class Packet
{
    private $content;
    
    /**
     * Constructor
     * @param string $content 
     */
    public function __construct($content = null)
    {
        $this->content = $content;
    }
    
    /**
     * Set content
     * 
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    /**
     * Add content to the packet
     * 
     * @param string $content 
     */
    public function addContent($content)
    {
        $this->content .= $content;
    }
    
    /**
     * Get content
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->content;
    }
    
    /**
     * Get length of packet content
     * 
     * @return int 
     */
    public function getLength()
    {
        return strlen($this->content);
    }
    
    /**
     * Get a byte from the packet content
     * 
     * @param bool $delByte If true, delete the byte that is return
     * @return byte 
     * @throws EmptyPacketException 
     */
    public function getByte($delByte = true)
    {
        if (empty($this->content)) throw new EmptyPacketException();
        
        // On récupère 1 byte
        $data = substr($this->content, 0, 1);
        $data = unpack('cval', $data);
        
        if ($delByte) $this->content = substr($this->content, 1);
        
        return $data['val'];
    }
    
    /**
     * Get a short from the packet content
     * 
     * @param bool $delShort If true, delete the short that is return
     * @return short
     * @throws EmptyPacketException 
     */
    public function getShort($delShort = true)
    {
        if (empty($this->content)) throw new EmptyPacketException();
        
        // On récupère les 2 bytes constituant l'entier court 
        $data = substr($this->content, 0, 2);
        $data = unpack('sval', $data);
        
        if ($delShort) $this->content = substr($this->content, 2);
        
        return $data['val'];
    }
    
    /**
     * Get a long from the packet content
     * 
     * @param bool $delLong If true, delete the long that is return
     * @return long
     * @throws EmptyPacketException 
     */
    public function getLong($delLong = true)
    {
        if (empty($this->content)) throw new EmptyPacketException();
        
        // On récupère les 4 bytes constituant l'entier long
        $data = substr($this->content, 0, 4);
        $data = unpack('lval', $data);
        
        if ($delLong) $this->content = substr($this->content, 4);
        
        return $data['val'];
    }
    
    /**
     * Get a integer from the packet content
     * 
     * @param bool $delInt If true, delete the integer that is return
     * @return integer
     * @throws EmptyPacketException 
     */
    public function getInt($delInt = true)
    {
        if (empty($this->content)) throw new EmptyPacketException();
        
        // On récupère les 4 bytes constituant l'entier
        $data = substr($this->content, 0, 4);
        $data = unpack('ival', $data);
        
        if ($delInt) $this->content = substr($this->content, 4);
        
        return $data['val'];
    }
    
    /**
     * Get a float from the packet content
     * 
     * @param bool $delFloat If true, delete the float that is return
     * @return float
     * @throws EmptyPacketException 
     */
    public function getFloat($delFloat = true)
    {
        if (empty($this->content)) throw new EmptyPacketException();
        
        // On récupère les 4 bytes constituant l'entier
        $data = substr($this->content, 0, 4);
        $data = unpack('fval', $data);
        
        if ($delFloat) $this->content = substr($this->content, 4);
        
        return $data['val'];
    }
    
    /**
     * Get the first string inside the packet content
     * 
     * @param bool $delString If true, delete the string that is return
     * @return string
     * @throws EmptyPacketException 
     */
    public function getString($delString = true)
    {
        if (empty($this->content)) throw new EmptyPacketException();
        
        // On recherche la première occurence du char 0x0 signant la fin
        $string = strstr($this->content, "\0", true);
        
        if ($delString) {
            $pos = strpos($this->content, "\0");
            $this->content = substr($this->content, $pos+1);
        }
        
        return $string;
    }
    
    /**
     * Extract all vars depending on type specified
     * 
     * @param array $vars
     * @return array 
     */
    public function extract(array $vars)
    {
        $return = array();
        
        foreach ($vars AS $varName => $varType) {
            $method = 'get' . ucfirst($varType);
            $return[$varName] = $this->$method();
        }
        
        return $return;
    }
}
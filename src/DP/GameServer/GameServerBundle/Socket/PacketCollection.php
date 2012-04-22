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
use DP\GameServer\GameServerBundle\Socket\Packet;
use DP\GameServer\GameServerNundle\Socket\Exception\InvalidPositionException;

/**
 * @author Albin Kerouanton 
 */
class PacketCollection implements \SeekableIterator, \ArrayAccess, \Countable
{
    private $pos = 0;
    private $array = array();
    
    /**
     * Get the current element of the collection
     * 
     * @return \DP\GameServer\GameServerBundle\Socket\Packet
     */
    public function current()
    {
        if (!$this->valid()) throw new InvalidPositionException('Can\'t access to an undefined packet in the collection.');
        
        return $this->array[$this->pos];
    }
    
    /**
     * Get the position in the array
     * 
     * @return int
     */
    public function key()
    {
        return $this->pos;
    }
    
    /**
     * Increment the current position
     */
    public function next()
    {
        ++$this->pos;
    }
    
    /**
     * Rewind the collection 
     */
    public function rewind()
    {
        $this->pos = 0;
    }
    
    /**
     * Verify if the current position is valid
     * 
     * @return bool
     */
    public function valid()
    {
        return isset($this->array[$this->pos]);
    }
    
    /**
     * Seek an element in the collection
     * 
     * @param int $pos
     * @throws InvalidPositionException 
     */
    public function seek($pos)
    {
        $oldPos = $this->pos;
        $this->pos = $pos;
        
        if (!$this->valid()) throw new InvalidPositionException('Can\'t access to an undefined packet in the collection.');
    }
    
    /**
     * Verify if the element defined by the key $key exists
     * 
     * @param int $key
     * @return bool 
     */
    public function offsetExists($key)
    {
        return isset($this->array[$key]);
    }
    
    /**
     * Get an array element defined by his $key
     * 
     * @param int $key
     * @return \DP\GameServer\GameServerBundle\Socket\Packet 
     */
    public function offsetGet($key)
    {
        return $this->array[$key];
    }
    
    /**
     * Set an array element
     * 
     * @param int $key
     * @param \DP\GameServer\GameServerBundle\Socket\Packet $val 
     */
    public function offsetSet($key, $val)
    {
        $this->array[$key] = $val;
    }
    
    /**
     * Supprime un élément de l'array
     * 
     * @param int $key 
     */
    public function offsetUnset($key)
    {
        unset($this->array[$key]);
    }
    
    /**
     * Count the array elements
     * 
     * @return int 
     */
    public function count()
    {
        return count($this->array);
    }
    
    /**
     * Reassemble all packets in one packet
     * 
     * @return \DP\GameServer\GameServerBundle\Socket\Packet 
     */
    public function reassemble($callback = null)
    {
        $bigPacket = new Packet();
        
        foreach ($this->array AS $packet) {
            $packet->rewind();
            
            if (is_callable($callback)) {
                $bigPacket = call_user_func($callback, $bigPacket, $packet);
            }
            else {
                $bigPacket->addContent($packet->getContent());
            }
        }
        
        return $bigPacket;
    }
    
    public function add(Packet $packet)
    {
        $this->array[] = $packet;
    }
}

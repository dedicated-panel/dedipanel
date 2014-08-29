<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    public function offsetSet($key, Packet $val)
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
        
        return $bigPacket->rewind();
    }
    
    public function add(Packet $packet)
    {
        $this->array[] = $packet;
    }
}

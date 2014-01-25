<?php

namespace DP\GameServer\GameServerBundle\FTP;

use DP\GameServer\GameServerBundle\FTP\AbstractItem;

class Directory extends AbstractItem implements \Iterator
{
    /** @var array $content **/
    private $content;
    /** @var integer $pos **/
    private $pos;
    
    
    public function __construct($path = null, $name = null, array $content = array())
    {
        $this->path    = rtrim($path, '/');
        $this->name    = $name;
        $this->content = $content;
        
        $this->invalid = false;
        $this->rewind();
    }
    
    /**
     * Set directory content, and rewind the iterator
     * 
     * @param array $content
     * 
     * @return \DP\GameServer\GameServerBundle\FTP\Directory
     */
    public function setContent(array $content)
    {
        $this->content = $content;
        
        $this->rewind();
        
        return $this;
    }
    
    /**
     * Get directory content
     * 
     * @return array
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Rewind the internal cursor
     */
    public function rewind()
    {
        $this->pos = 0;
        
        return $this;
    }
    
    /**
     * Get the current element
     * 
     * @return \DP\GameServer\GameServerBundle\FTP\Directory|\DP\GameServer\GameServerBundle\FTP\File
     */
    public function current()
    {
        return $this->content[$this->pos];
    }
    
    /**
     * Get the current position
     * 
     * @return integer
     */
    public function key()
    {
        return $this->pos;
    }
    
    /**
     * Go to the next element
     * 
     * @return \DP\GameServer\GameServerBundle\FTP\Directory
     */
    public function next()
    {
        ++$this->pos;
        
        return $this;
    }
    
    /**
     * Is the current position valid ?
     * Called right after next foreach loop
     * 
     * @return boolean
     */
    public function valid()
    {
        return isset($this->content[$this->pos]);
    }
}

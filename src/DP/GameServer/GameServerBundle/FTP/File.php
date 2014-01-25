<?php

namespace DP\GameServer\GameServerBundle\FTP;

use DP\GameServer\GameServerBundle\FTP\AbstractItem;

class File extends AbstractItem
{
    /** @var string $content **/
    private $content;
    
    
    public function __construct($path = null, $name = null, $content = "")
    {
        $this->path    = $path;
        $this->name    = $name;
        $this->content = $content;
        
        $this->invalid = false;
    }
    
    /**
     * Set directory content
     * 
     * @param string $content
     * 
     * @return \DP\GameServer\GameServerBundle\FTP\Directory
     */
    public function setContent($content)
    {
        $this->content = $content;
        
        return $this;
    }
    
    /**
     * Get directory content
     * 
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}

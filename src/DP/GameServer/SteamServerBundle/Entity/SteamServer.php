<?php

namespace DP\GameServer\SteamServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\GameServer\GameServerBundle\Entity\GameServer;

/**
 * DP\GameServer\SteamServerBundle\Entity\SteamServer
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="DP\GameServer\SteamServerBundle\Entity\SteamServerRepository")
 */
class SteamServer extends GameServer {
    /**
     * @var integer $autoReboot
     *
     * @ORM\Column(name="autoReboot", type="integer", nullable=true)
     */
    private $autoReboot;

    /**
     * @var string $rcon
     *
     * @ORM\Column(name="rcon", type="string", length=32, nullable=true)
     */
    private $rcon;

    /**
     * @var boolean $munin
     *
     * @ORM\Column(name="munin", type="boolean", nullable=true)
     */
    private $munin;

    /**
     * @var string $sv_passwd
     *
     * @ORM\Column(name="sv_passwd", type="string", length=16, nullable=true)
     */
    private $sv_passwd;

    /**
     * @var integer $core
     *
     * @ORM\Column(name="core", type="integer", nullable=true)
     */
    private $core;
    
    // Write default values for Steam Servers
    protected $maxplayers = 12;
    protected $port = 27015;
    
    
    /**
     * Set autoReboot
     *
     * @param integer $autoReboot
     */
    public function setAutoReboot($autoReboot)
    {
        $this->autoReboot = $autoReboot;
    }

    /**
     * Get autoReboot
     *
     * @return integer 
     */
    public function getAutoReboot()
    {
        return $this->autoReboot;
    }

    /**
     * Set rcon
     *
     * @param string $rcon
     */
    public function setRcon($rcon)
    {
        $this->rcon = $rcon;
    }

    /**
     * Get rcon
     *
     * @return string 
     */
    public function getRcon()
    {
        return $this->rcon;
    }

    /**
     * Set munin
     *
     * @param boolean $munin
     */
    public function setMunin($munin)
    {
        $this->munin = $munin;
    }

    /**
     * Get munin
     *
     * @return boolean 
     */
    public function getMunin()
    {
        return $this->munin;
    }

    /**
     * Set sv_passwd
     *
     * @param string $svPasswd
     */
    public function setSvPasswd($svPasswd)
    {
        $this->sv_passwd = $svPasswd;
    }

    /**
     * Get sv_passwd
     *
     * @return string 
     */
    public function getSvPasswd()
    {
        return $this->sv_passwd;
    }

    /**
     * Set core
     *
     * @param integer $core
     */
    public function setCore($core)
    {
        $this->core = $core;
    }

    /**
     * Get core
     *
     * @return integer 
     */
    public function getCore()
    {
        return $this->core;
    }
}
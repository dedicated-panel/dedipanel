<?php

namespace DP\GameServer\SteamServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\GameServer\GameServerBundle\Entity\GameServer;
use DP\Core\MachineBundle\PHPSeclibWrapper\PHPSeclibWrapper;

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
    
    public function getAbsoluteDir() {
        return $this->machine->getHome() . '/' . $this->dir . '/';
    }
    
    public function installServer(\Twig_Environment $twig) {
        $installDir = $this->getAbsoluteDir();
        $scriptPath = $installDir . 'install.sh';
        $logPath = $installDir . 'install.log';
        $screenName = 'install-' . $this->dir;
        $installName = $this->game->getInstallName();
        
        $mkdirCmd = 'if [ ! -e ' . $installDir . ' ]; then mkdir ' . $installDir . '; fi';
        $screenCmd = 'screen -dmS ' . $screenName . ' ' . $scriptPath . ' "' . $installName . '"';
//        $screenCmd .= ' > ' . $logPath . ' 2>&1';
        
        $installScript = $twig->render('DPSteamServerBundle:sh:install.sh.twig', 
            array('serv' => $this));
        
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        $sec->exec($mkdirCmd);
        $sec->upload($scriptPath, $installScript);
        $sec->exec($screenCmd);
        
        $this->installationStatus = 0;
    }
}
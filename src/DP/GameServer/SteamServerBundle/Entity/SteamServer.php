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

namespace DP\GameServer\SteamServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\GameServer\GameServerBundle\Entity\GameServer;
use DP\Core\MachineBundle\PHPSeclibWrapper\PHPSeclibWrapper;
use DP\GameServer\SteamServerBundle\SteamQuery\SteamQuery;
use DP\Core\GameBundle\Entity\Plugin;

/**
 * DP\GameServer\SteamServerBundle\Entity\SteamServer
 *
 * @ORM\Table(name="steamserver")
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
     * @ORM\Column(name="rconPassword", type="string", length=32, nullable=true)
     */
    private $rconPassword;

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
     * @var \Doctrine\Common\Collections\ArrayCollection $plugins
     * 
     * @ORM\ManyToMany(targetEntity="DP\Core\GameBundle\Entity\Plugin") 
     * @ORM\JoinTable(name="steamserver_plugins",
     *      joinColumns={@ORM\JoinColumn(name="server_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="plugin_id", referencedColumnName="id")}
     * )
     */
    private $plugins;
    
    /**
     * @var integer $hltvPort
     * 
     * @ORM\Column(name="hltvPort", type="integer", nullable=true)
     */
    private $hltvPort;
    
    private $rcon;
    
    
    public function __construct()
    {
        $this->plugins = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set rconPassword
     *
     * @param string $rconPassword
     */
    public function setRconPassword($rconPassword)
    {
        $this->rconPassword = $rconPassword;
    }

    /**
     * Get rconPassword
     *
     * @return string 
     */
    public function getRconPassword()
    {
        return $this->rconPassword;
    }
    
    public function isEmptyRconPassword()
    {
        return empty($this->rconPassword);
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
    
    /**
     * Set HLTV/SRCTV Port
     * 
     * @param integer $hltvPort 
     */
    public function setHltvPort($hltvPort)
    {
        $this->hltvPort = $hltvPort;
    }
   
    /**
     * Get HLTV/SRCTV Port
     * 
     * @return integer
     */
    public function getHltvPort()
    {
        return $this->hltvPort;
    }
    
    /**
     * Get absolute path of server installation directory
     * 
     * @return string
     */
    public function getAbsoluteDir()
    {
        return $this->machine->getHome() . '/' . $this->getDir() . '/';
    }
    
    /**
     * Get absolute path of binaries directory
     * 
     * @return string
     */
    private function getAbsoluteBinDir()
    {        
        return $this->getAbsoluteDir() . $this->game->getBinDir(); 
   }
    
    /**
     * Get absolute path of game content directory
     * 
     * @return string
     */
    private function getAbsoluteGameContentDir()
    {
        return $this->getAbsoluteBinDir() . $this->game->getLaunchName() . '/';
    }
    
    /**
     * Upload & launch game server installation
     * 
     * @param \Twig_Environment $twig Used for generate shell script
     */
    public function installServer(\Twig_Environment $twig)
    {
        $installDir = $this->getAbsoluteDir();
        $scriptPath = $installDir . 'install.sh';
        $logPath = $installDir . 'install.log';
        $screenName = 'install-' . $this->getDir();
        $installName = $this->game->getInstallName();
        
        $mkdirCmd = 'if [ ! -e ' . $installDir . ' ]; then mkdir ' . $installDir . '; fi';
        $screenCmd  = 'screen -dmS ' . $screenName . ' ' . $scriptPath . ' "' . $installName . '"';
        
        $installScript = $twig->render('DPSteamServerBundle:sh:install.sh.twig', 
            array('installDir' => $installDir));
        
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        $sec->exec($mkdirCmd);
        $sec->upload($scriptPath, $installScript);
        $sec->exec($screenCmd);
        
        $this->installationStatus = 0;
    }  
    
    public function removeInstallationFiles()
    {
        $installDir = $this->getAbsoluteDir();
        $scriptPath = $installDir . 'install.sh';
        $logPath = $installDir . 'install.log';
        
        return PHPSeclibWrapper::getFromMachineEntity($this->getMachine())
                ->exec('rm -f ' . $scriptPath . ' ' . $logPath);
    }
    
    public function getGameInstallationProgress()
    {
        $absDir = $this->getAbsoluteDir();
        $logPath = $absDir . 'install.log';
        
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        $installLog = $sec->exec('cat ' . $logPath);
        
        if (strpos($installLog, 'Install ended') !== false) {
            // Si l'installation est terminé, on supprime le fichier de log et le script
            $sec->exec('rm -f' . $absDir . 'install.log ' . $absDir . 'install.sh');
           return 100; // 101 == serveur installé
        }
        elseif (strpos($installLog, 'Game install') !== false) {
            // Si on en est rendu au téléchargement des données, 
            // On récupère le pourcentage du dl dans le screen
            // Pour l'afficher à l'utilisateur
            $tmpFile = '/tmp/' . uniqid();
            $cmd = 'screen -S install-' . $this->getDir() . ' -X hardcopy ' . $tmpFile . '; sleep 1s;';
            $cmd .= 'if [ -e ' . $tmpFile . ' ]; then cat ' . $tmpFile . '; rm -f ' . $tmpFile . '; fi';
            
            $screenContent = $sec->exec($cmd);
            
            if ($screenContent == 'No screen session found.') return null;
            else {
                // Si on a réussi à récupérer le contenu du screen, 
                // On recherche dans chaque ligne en commencant par la fin
                // Un signe "%" afin de connaître le % le plus à jour
                $lines = array_reverse(explode("\n", $screenContent));
                
                foreach ($lines AS $line) {                    
                    $percentPos = strpos($line, '%');
                    
                    if ($percentPos !== false) {
                        return substr($line, $percentPos-5, 5);
                    }
                }
            }
        }
        elseif (strpos($installLog, 'Steam updating')) {
            return 2;
        }
        elseif (strpos($installLog, 'DL hldsupdatetool.bin')) {
            return 1;
        }
        else {
            return 0;
        }
    }
    
    public function uploadShellScripts(\Twig_Environment $twig)
    {        
        $game = $this->getGame();
        $machine = $this->getMachine();
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        
        /** HLDS.sh **/
        $screenName = $machine->getUser() . '-' . $this->getDir();
        $scriptPath = $this->getAbsoluteDir() . 'hlds.sh';
        
        $hldsScript = $twig->render('DPSteamServerBundle:sh:hlds.sh.twig', array(
            'screenName' => $screenName, 'bin' => $game->getBin(), 
            'launchName' => $game->getLaunchName(), 'ip' => $machine->getPublicIp(), 
            'port' => $this->getPort(), 'maxplayers' => $this->getMaxplayers(), 
            'startMap' => $game->getMap(), 'binDir' => $this->getAbsoluteBinDir(), 
        ));
        $uploadHlds = $sec->upload($scriptPath, $hldsScript, 0750);
//        echo'<pre>'; print_r($sec->getSFTP()->getSFTPLog()); echo'</pre>';
        
        /** HLTV.sh **/
        $uploadHltv = true;
        
        if ($game->getBin() == 'hlds_run') {
            $screenName = $machine->getUser() . '-hltv-' . $this->getDir();
            $scriptPath = $this->getAbsoluteDir() . 'hltv.sh';

            $hltvScript = $twig->render('DPSteamServerBundle:sh:hltv.sh.twig', array(
                'binDir' => $this->getAbsoluteBinDir(), 
                'screenName' => $this->getHltvScreenName(), 
            ));
            $uploadHltv = $sec->upload($scriptPath, $hltvScript, 0750);
        }
        
        // On upload un fichier server.cfg si aucun n'existe
        $cfgPath = $this->getAbsoluteGameContentDir();
        if ($game->isSource() || $game->isOrangebox()) {
            $cfgPath .= 'cfg/';
        }
        $cfgPath .= 'server.cfg';
        $sec->exec('if [ ! -e ' . $cfgPath . ' ]; then touch ' . $cfgPath . '; fi');
        
        $this->installationStatus = 101;
        
        return $uploadHlds && $uploadHltv;
    }
    
    public function changeStateServer($state)
    {
        $scriptPath = $this->getAbsoluteDir() . 'hlds.sh';
        
        return PHPSeclibWrapper::getFromMachineEntity($this->getMachine())
                ->exec($scriptPath . ' ' . $state);
    }
    
    public function setQuery(SteamQuery $query)
    {
        $this->query = $query;
    }
    
    public function getQuery()
    {
        return $this->query;
    }
    
    public function setRcon($rcon)
    {
        $this->rcon = $rcon;
    }
    
    public function getRcon()
    {
        return $this->rcon;
    }
    
    /**
     * Add plugin
     * 
     * @param \DP\Core\GameBundle\Entity\Plugin $plugin 
     */
    public function addPlugin(\DP\Core\GameBundle\Entity\Plugin $plugin)
    {
        $this->plugins[] = $plugin;
    }
    
    /**
     * Remove a server plugin
     * @param \DP\Core\GameBundle\Entity\Plugin $plugin 
     */
    public function removePlugin(\DP\Core\GameBundle\Entity\Plugin $plugin)
    {
        $this->plugins->removeElement($plugin);
    }
    
    /**
     * Get plugins recorded as "installed on the server"
     * 
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPlugins()
    {
        if ($this->plugins instanceof \Doctrine\ORM\PersistentCollection) {
            return $this->plugins->getValues();
        }
        else {
            return $this->plugins;
        }
    }
    
    public function getInstalledPlugins()
    {
        return $this->getPlugins();
    }
    
    public function getNotInstalledPlugins()
    {
        $intersectCallback = function ($plugin1, $plugin2) {
            return $plugin1->getId() - $plugin2->getId();
        };
        $plugins = $this->getGame()->getPlugins()->getValues();
        
        // On compare l'array contenant l'ensemble des plugins dispo pour le jeu
        // A ceux installés sur le serveur
        return array_udiff($plugins, $this->getPlugins(), $intersectCallback);
    }
    
    public function execPluginScript(\Twig_Environment $twig, Plugin $plugin, $action)
    {
        $dir = $this->getAbsoluteGameContentDir();
        $scriptName = $plugin->getScriptName();
        $scriptPath = $dir . $scriptName . '.sh';
        
        $screenName = $scriptName . '-' . $this->getDir();
        $screenCmd  = 'screen -dmS ' . $screenName . ' ' . $scriptPath . ' ' . $action;
        if ($action == 'install') $screenCmd .= ' "' . $plugin->getDownloadUrl () . '"';
        
        $pluginScript = $twig->render(
            'DPSteamServerBundle:sh:Plugins/' . $scriptName . '.sh.twig', array('gameDir' => $dir));
        
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        $sec->upload($scriptPath, $pluginScript);
        $sec->exec($screenCmd);
    }
    
    public function getDirContent($path = '')
    {
        $path = $this->getAbsoluteGameContentDir() . $path;
        $sftp = PHPSeclibWrapper::getFromMachineEntity($this->getMachine())->getSFTP();
        
        $dirContent = $sftp->rawlist($path);
        $dirs = array();
        $files = array();
        
        foreach ($dirContent AS $key => $attr) {
            $attr['name'] = $key;
            
            if ($attr['type'] == NET_SFTP_TYPE_DIRECTORY
                && $key != '..' && $key != '.') {
                $dirs[] = $attr;
            }
            elseif ($attr['type'] == NET_SFTP_TYPE_REGULAR) {
                $files[] = $attr;
            }
        }
        
        return array('files' => $files, 'dirs' => $dirs);
    }
    
    public function getFileContent($path)
    {
        $path = $this->getAbsoluteGameContentDir() . $path;
        
        return PHPSeclibWrapper::getFromMachineEntity($this->getMachine())
                ->getRemoteFile($path);
    }
    
    public function uploadFile($path, $content)
    {
        $path = $this->getAbsoluteGameContentDir() . $path;
        
        return PHPSeclibWrapper::getFromMachineEntity($this->getMachine())
                ->upload($path, $content, false);
    }
    
    public function touch($file)
    {
        $path = $this->getAbsoluteGameContentDir() . $file;
        
        return PHPSeclibWrapper::getFromMachineEntity($this->getMachine())
                ->touch($path);
    }
    
    public function getHltvScreenName()
    {
        return 'hltv-' . $this->getMachine()->getUser() . '-' . $this->getDir();
    }
    
    public function getHltvStatus()
    {        
        $status = PHPSeclibWrapper::getFromMachineEntity($this->getMachine())
                ->getSSH()->exec($this->getAbsoluteBinDir() . 'hltv.sh status');
        
        if (trim($status) == 'HLTV running.') return true;
        else return false;
    }
    
    public function startHltv($servIp, $servPort, $password = null, $record = null, $reload = false)
    {
        if ($password == null) {
            $password = '';
        }
        
        if ($this->game->isSource()) {
            $rcon = $this->getRcon();

            $exec = $rcon->sendCmd('exec hltv.cfg');

            if ($exec !== false && $reload == true) {
                return $rcon->sendCmd('reload');
            }
            else {
                return $exec;
            }
        }
        else {
            $cmd = 'screen -dmS ' . $this->getHltvScreenName() . ' ' 
                . $this->getAbsoluteBinDir() . 'hltv.sh start ' 
                . $servIp . ':' . $servPort . ' ' . $this->hltvPort . ' "' . $password . '"';
            if ($record != null) {
                $cmd .= ' ' . $record; 
            }

            return PHPSeclibWrapper::getFromMachineEntity($this->getMachine())
                ->getSSH()->exec($cmd);
        }
    }
    
    public function stopHltv()
    {
        if ($this->getGame()->isSource()) {
            return $this->getRcon()->sendCmd('tv_enable 0; tv_stop');
        }
        else {
            return PHPSeclibWrapper::getFromMachineEntity($this->getMachine())
                ->getSSH()->exec($this->getAbsoluteBinDir() . 'hltv.sh stop');
        }
    }
}
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
use DP\Core\GameBundle\Entity\Plugin;

/**
 * DP\GameServer\SteamServerBundle\Entity\SteamServer
 *
 * @ORM\Table(name="steam_server")
 * @ORM\Entity(repositoryClass="DP\GameServer\SteamServerBundle\Entity\SteamServerRepository")
 */
class SteamServer extends GameServer {
    /**
     * @var integer $rebootAt
     *
     * @ORM\Column(name="rebootAt", type="time", nullable=true)
     */
    private $rebootAt;

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
     * @var integer $hltvPort
     * 
     * @ORM\Column(name="hltvPort", type="integer", nullable=true)
     */
    private $hltvPort;
    
    /**
     * Set rebootAt
     *
     * @param \DateTime $rebootAt
     */
    public function setRebootAt($rebootAt)
    {
        $this->rebootAt = $rebootAt;
    }

    /**
     * Get rebootAt
     *
     * @return \DateTime 
     */
    public function getRebootAt()
    {
        return $this->rebootAt;
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
     * Upload & launch game server installation
     * 
     * @param \Twig_Environment $twig Used for generate shell script
     */
    public function installServer(\Twig_Environment $twig)
    {
        $installDir = $this->getAbsoluteDir();
        $scriptPath = $installDir . 'install.sh';
        $logPath = $installDir . 'install.log';
        $screenName = $this->getInstallScreenName();
        $installName = $this->game->getInstallName();
        
        $mkdirCmd = 'if [ ! -e ' . $installDir . ' ]; then mkdir ' . $installDir . '; fi';
        $screenCmd  = 'screen -dmS ' . $screenName . ' ' . $scriptPath . ' "' . $installName . '"';
        
        $installScript = $twig->render(
            'DPSteamServerBundle:sh:install.sh.twig', 
            array('installDir' => $installDir)
        );
        
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
    
    public function getInstallationProgress()
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
            $cmd = 'screen -S ' . $this->getInstallScreenName() . ' -X hardcopy ' . $tmpFile . '; sleep 1s;';
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
        // Upload du script de gestion du serveur de jeu
        $uploadHlds = $this->uploadHldsScript($twig);
        
        // Upload du script de gestion de l'hltv
        $uploadHltv = $this->uploadHltvScript($twig);
        
        // Création d'un ficier server.cfg vide (si celui-ci n'existe pas)
        $this->createDefaultServerCfgFile();
        
        $this->installationStatus = 101;
        
        return $uploadHlds && $uploadHltv;
    }
    
    public function uploadHldsScript(\Twig_Environment $twig)
    {
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        $game = $this->getGame();
        
        $scriptPath = $this->getAbsoluteHldsScriptPath();
        
        $hldsScript = $twig->render('DPSteamServerBundle:sh:hlds.sh.twig', array(
            'screenName' => $this->getScreenName(), 'bin' => $game->getBin(), 
            'launchName' => $game->getLaunchName(), 'ip' => $this->getMachine()->getPublicIp(), 
            'port' => $this->getPort(), 'maxplayers' => $this->getMaxplayers(), 
            'startMap' => $game->getMap(), 'binDir' => $this->getAbsoluteBinDir(), 
            'core' => $this->getCore(), 
        ));
        
        $uploadHlds = $sec->upload($scriptPath, $hldsScript, 0750);
//        echo'<pre>'; print_r($sec->getSFTP()->getSFTPLog()); echo'</pre>';
        
        return $uploadHlds;
    }
    
    public function uploadHltvScript(\Twig_Environment $twig)
    {        
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        $scriptPath = $this->getAbsoluteDir() . 'hltv.sh';
        
        // Supression du fichier (s'il exsite déjà)
        $sec->exec('if [ -e ' . $scriptPath . ' ]; then rm ' . $scriptPath . '; fi');

        // Création du fichier hltv.sh (uniquement si c'est un jeu GoldSrc)
        if ($this->getGame()->getBin() == 'hlds_run') {
            $hltvScript = $twig->render('DPSteamServerBundle:sh:hltv.sh.twig', array(
                'binDir' => $this->getAbsoluteBinDir(), 
                'screenName' => $this->getHltvScreenName(), 
            ));
            $uploadHltv = $sec->upload($scriptPath, $hltvScript, 0750);
        }
        else {
            $uploadHltv = true;
        }
        
        return $uploadHltv;
    }
    
    public function createDefaultServerCfgFile()
    {
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        
        // On upload un fichier server.cfg si aucun n'existe
        $cfgPath = $this->getAbsoluteGameContentDir();
        if ($this->getGame()->isSource() || $this->getGame()->isOrangebox()) {
            $cfgPath .= 'cfg/';
        }
        $cfgPath .= 'server.cfg';
        
        return $sec->exec('if [ ! -e ' . $cfgPath . ' ]; then touch ' . $cfgPath . '; fi');
    }
    
    public function uploadDefaultServerCfgFile()
    {
        $template = $this->getGame()->getConfigTemplate();
        
        if (!empty($template)) {
            $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
            $cfgPath = $this->getServerCfgPath();
            
            $env = new \Twig_Environment(new \Twig_Loader_String());
            $cfgFile = $env->render($template, array(
                'hostname' => $this->getServerName(), 
            ));

            return $sec->upload($cfgPath, $cfgFile, 0750);
        }
        
        return false;
    }
    
    public function modifyServerCfgFile()
    {
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        $cfgPath = $this->getServerCfgPath();
        
        $remoteFile = $sec->getRemoteFile($cfgPath);
        $fileLines = explode("\r\n", $remoteFile);
        
        $pattern = '#^hostname "(.+)"$#';
        $replacement = 'hostname "' . $this->getServerName() . '"';
        
        foreach ($fileLines AS &$line) {
            if ($line == '' || substr($line, 0, 2) == '//') continue;
            
            if (preg_match($pattern, $line)) {
                $line = preg_replace($pattern, $replacement, $line);
            }
        }
        // Suppression de la référence
        unset($line);
        
        // Upload du nouveau fichier
        return $sec->upload($cfgPath, implode("\r\n", $fileLines));
    }
    
    public function changeStateServer($state)
    {
        return PHPSeclibWrapper::getFromMachineEntity($this->getMachine())
                ->exec($this->getAbsoluteHldsScriptPath() . ' ' . $state);
    }

    public function execPluginScript(\Twig_Environment $twig, Plugin $plugin, $action)
    {
        $dir = $this->getAbsoluteGameContentDir();
        $scriptName = $plugin->getScriptName();
        $scriptPath = $dir . $scriptName . '.sh';
        
        $screenName = $this->getPluginInstallScreenName($scriptName);
        $screenCmd  = 'screen -dmS ' . $screenName . ' ' . $scriptPath . ' ' . $action;
        
        if ($action == 'install') {
            $screenCmd .= ' "' . $plugin->getDownloadUrl () . '"';
        }
        
        $pluginScript = $twig->render(
            'DPSteamServerBundle:sh:Plugin/' . $scriptName . '.sh.twig', array('gameDir' => $dir));
        
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        $sec->upload($scriptPath, $pluginScript);
        $sec->exec($screenCmd);
    }
    
    public function getHltvScreenName()
    {        
        return sha1('hltv-' . $this->getMachine()->getUser() . '-' . $this->getDir(), true);
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
                ->exec($this->getAbsoluteBinDir() . 'hltv.sh stop');
        }
    }
    
    protected function getAbsoluteGameContentDir()
    {
        return $this->getAbsoluteBinDir() . $this->game->getLaunchName() . '/';
    }
    
    public function getAbsoluteHldsScriptPath()
    {
        return $this->getAbsoluteDir() . 'hlds.sh';
    }
    
    public function addAutoReboot()
    {
        $hldsScriptPath = $this->getAbsoluteHldsScriptPath();
        $rebootTime = $this->getRebootAt();
        
        $crontabLine  = $rebootTime->format('i H') . ' * * * ' . $hldsScriptPath;
        $crontabLine .= ' restart >> ' . $this->getAbsoluteDir() . 'cron-dp.log';
        
        return $this->getMachine()->updateCrontab($hldsScriptPath, $crontabLine);
    }
    
    public function removeAutoReboot()
    {
        return $this->getMachine()->removeFromCrontab($this->getAbsoluteHldsScriptPath());
    }
    
    public function getServerCfgPath()
    {
        $cfgPath = $this->getAbsoluteGameContentDir();
        if ($this->getGame()->isSource() || $this->getGame()->isOrangebox()) {
            $cfgPath .= 'cfg/';
        }
        
        return $cfgPath . 'server.cfg';
    }
    
    public function getServerName()
    {
        return $this->getName();
    }

    public function removeServer()
    {
        $screenName = $this->getScreenName();
        $scriptPath = $this->getAbsoluteHldsScriptPath();
        $serverPath = $this->getAbsoluteDir();
        
        // On commence par vérifier que le serveur n'est pas lancé (sinon on l'arrête)        
        $cmd  = 'if [ `pgrep -cf "' . $screenName . '"` != 0 ]; then ';
        $cmd .= $scriptPath . ' stop; fi; ';
        // Puis on supprime complètement le dossier du serveur
        $cmd .= 'rm -Rf ' . $serverPath;
        
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        $sec->exec($cmd);
    }
}

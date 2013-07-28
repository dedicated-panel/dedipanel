<?php

/*
** Copyright (C) 2010-2013 Kerouanton Albin, Smedts Jérôme
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
use PHPSeclibWrapper\Exception\MissingPacketException;
use DP\Core\GameBundle\Entity\Plugin;
use DP\GameServer\SteamServerBundle\Exception\InstallAlreadyStartedException;

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
     * @var string $mode
     * 
     * @ORM\Column(name="mode", type="string", nullable=true)
     */
    private $mode;

    /**
     * Set rebootAt
     *
     * @param \DateTime $rebootAt
     * 
     * @return SteamServer
     */
    public function setRebootAt($rebootAt)
    {
        $this->rebootAt = $rebootAt;
        
        return $this;
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
     * 
     * @return SteamServer
     */
    public function setMunin($munin)
    {
        $this->munin = $munin;
        
        return $this;
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
     * 
     * @return SteamServer
     */
    public function setSvPasswd($svPasswd)
    {
        $this->sv_passwd = $svPasswd;
        
        return $this;
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
     * 
     * @return SteamServer
     */
    public function setCore($core)
    {
        $this->core = $core;
        
        return $this;
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
     * 
     * @return SteamServer
     */
    public function setHltvPort($hltvPort)
    {
        $this->hltvPort = $hltvPort;
        
        return $this;
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
     * Set game server mode 
     * 
     * @param string $mode
     * 
     * @return SteamServer
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        
        return $this;
    }
    
    /**
     * Get game server mode
     * 
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }
    
    public static function getModeList()
    {
        return array(
            '0;0' => 'Classic Casual', 
            '0;1' => 'Classic Competitive', 
            '1;0' => 'Arms Race', 
            '1;1' => 'Demolition', 
            '1;2' => 'Deathmatch', 
        );
    }

    /**
     * Upload & launch game server installation
     *
     * @param \Twig_Environment $twig Used for generate shell script
     */
    public function installServer(\Twig_Environment $twig)
    {
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());

        // S'il s'agit d'un serveur 64 bits on commence par vérifier si le paquet ia32-libs est présent
        // (nécessaire pour l'utilisation de l'installateur steam)
        if ($this->machine->getIs64Bit() === true) {
            if ($sec->hasCompatLib() == false) {
                throw new MissingPacketException($sec, 'ia32-libs');
            }
        }

        $installDir = $this->getAbsoluteDir();
        $scriptPath = $installDir . 'install.sh';
        $logPath = $installDir . 'install.log';
        $screenName = $this->getInstallScreenName();
        $steamCmd = $this->game->getSteamCmd();
        $installName = $this->game->getInstallName();

        if($steamCmd != 0) {
            $installName = '' . $this->game->getappId() . '.' . $this->game->getappMod() .'';
        }

        $mkdirCmd = 'if [ ! -e ' . $installDir . ' ]; then mkdir ' . $installDir . '; fi';

        $pgrep = '`ps aux | grep SCREEN | grep "' . $screenName . ' " | grep -v grep | wc -l`';
        $screenCmd  = 'if [ ' . $pgrep . ' = "0" ]; then ';
        $screenCmd .= 'screen -dmS "' . $screenName . '" ' . $scriptPath . ' "' . $steamCmd . '" "' . $installName . '"; ';
        $screenCmd .= 'else echo "Installation is already in progress."; fi; ';

        $installScript = $twig->render(
            'DPSteamServerBundle:sh:install.sh.twig',
            array('installDir'  => $installDir)
        );

        $sec->exec($mkdirCmd);
        $sec->upload($scriptPath, $installScript);
        $result = $sec->exec($screenCmd);

        if ($result == 'Installation is already in progress.') {
            throw new InstallAlreadyStartedException();
        }

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
        $logCmd = 'if [ -f ' . $logPath . ' ]; then cat ' . $logPath . '; else echo "File not found exception."; fi; ';

        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        $installLog = $sec->exec($logCmd);

        if (strpos($installLog, 'Install ended') !== false) {
            // Si l'installation est terminé, on supprime le fichier de log et le script
            $sec->exec('rm -f ' . $absDir . 'install.log ' . $absDir . 'install.sh');
           // 100 = serveur installé
           // 101 = serveur installé + config uploadé
           return 100;
        }
        elseif (strpos($installLog, 'Game install') !== false) {
            $screenContent = $sec->getScreenContent($this->getInstallScreenName());

            if ($screenContent == 'No screen session found.') return null;
            else {
                // Si on a réussi à récupérer le contenu du screen,
                // On recherche dans chaque ligne en commencant par la fin
                // Un signe "%" afin de connaître le % le plus à jour
                $lines = array_reverse(explode("\n", $screenContent));

                foreach ($lines AS $line) {
                    // On passe à la ligne suivante si l'actuelle est vide
                    if (empty($line)) continue;

                    $line = trim($line);
                    
                    if ($this->getGame()->getSteamCmd()) {
                        $matches = array();
                        
                        if (preg_match('#^App state \(0x\d+\) downloading, progress: ([\d]+,[\d]+)#', $line ,$matches)) {
                            return $matches[1];
                        }
                    }
                    else {
                        $percentPos = strpos($line, '%');
    
                        if ($percentPos !== false) {
                            $percent = substr($line, $percentPos-5, 5);
                            $percent = ($percent > 3) ? $percent : 3;
    
                            return $percent;
                        }
                    }
                }

                // Si arrivé à ce stade aucun pourcentage n'a été détecté
                // C'est surement que l'installation est en train de vérifier les fichiers locaux
                return 3;
            }
        }
        elseif (strpos($installLog, 'Steam updating')) {
            return 2;
        }
        elseif (strpos($installLog, 'DL hldsupdatetool.bin') || strpos($installLog, 'Download steamcmd')) {
            return 1;
        }
        elseif ($installLog == 'File not found exception.') {
            return null;
        }
        else {
            throw new \ErrorException('Impossible de définir le statut de l\'installation du serveur.');
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
        
        if ($this->getGame()->getLaunchName() == 'csgo') {
            $this->modifyGameModesCfg();
        }

        $this->installationStatus = 101;

        return $uploadHlds && $uploadHltv;
    }

    public function uploadHldsScript(\Twig_Environment $twig)
    {
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        $game = $this->getGame();

        $scriptPath = $this->getAbsoluteHldsScriptPath();
        $core = $this->getCore();
        $isCsgo = $this->getGame()->getLaunchName() == 'csgo';
        $gameType = '';
        $gameMode = '';
        $mapGroup = '';
        
        if (!empty($core)) {
            $core -= 1;
        }
        
        if ($isCsgo) {
            $mode = $this->getMode();
            $mode = !(empty($mode)) ? $mode : '0;0';
            
            list($gameType, $gameMode) = explode(';', $mode);
            
            if ($gameType == 0 && $gameMode == 0) {
                $mapGroup = 'mg_bomb';
            }
            elseif ($gameType == 0 && $gameMode == 1) {
                $mapGroup = 'mg_bomb_se';
            }
            elseif ($gameType == 1 && $gameMode == 0) {
                $mapGroup = 'mg_armsrace';
            }
            elseif ($gameType == 1 && $gameMode == 1) {
                $mapGroup = 'mp_demolition';
            }
            elseif ($gameType == 1 && $gameMode == 2) {
                $mapGroup = 'mg_allclassic';
            }
        }

        $hldsScript = $twig->render('DPSteamServerBundle:sh:hlds.sh.twig', array(
            'screenName' => $this->getScreenName(), 'bin' => $game->getBin(),
            'launchName' => $game->getLaunchName(), 'ip' => $this->getMachine()->getPublicIp(),
            'port' => $this->getPort(), 'maxplayers' => $this->getMaxplayers(),
            'startMap' => $game->getMap(), 'binDir' => $this->getAbsoluteBinDir(),
            'core' => $core, 'isCsgo' => $isCsgo, 'gameType' => $gameType, 'gameMode' => $gameMode, 
            'mapGroup' => $mapGroup,  
            ''
        ));

        $uploadHlds = $sec->upload($scriptPath, $hldsScript, 0750);

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
        $cfgPath = $this->getServerCfgPath();

        if ($this->getGame()->getLaunchName() == 'csgo') {
            $file = $this->getAbsoluteGameContentDir() . 'gamemodes_server.txt';
            
            $ret = $sec->exec('if [ ! -e ' . $file . ' ] && [ -e ' . $file . '.example ]; then mv ' . $file . '.example ' . $file . '; fi');
            
            return $ret;
        }
        else {
            // On créer un fichier server.cfg si aucun n'existe
            return $sec->exec('if [ ! -e ' . $cfgPath . ' ]; then touch ' . $cfgPath . '; fi');
        }
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
        
        $patterns = array(
            '#^hostname "(.+)"$#' => 'hostname "' . $this->getServerName() . '"',
        );
        $matched = array();

        foreach ($fileLines AS &$line) {
            if ($line == '' || substr($line, 0, 2) == '//') continue;

            // Vérifie tous les patterns fournis
            foreach ($patterns AS $pattern => $replacement) {
                // Si le pattern est trouvé, le replacement est effectué
                // Et la ligne est ajouté à l'array des lignes détectés
                if (preg_match($pattern, $line)) {
                    $line = preg_replace($pattern, $replacement, $line);
                    
                    $matched[$pattern] = $replacement;
                }
            }
        }
        // Suppression de la référence
        unset($line);
        
        // Ajoute les lignes non matchées
        $delta = array_diff($patterns, $matched);
        foreach ($delta AS $toAdd) {
            $fileLines[] = $toAdd;
        }

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
        if ($action != 'install' && $action != 'uninstall' && $action != 'activate' && $action != 'deactivate') {
            throw new BadMethodCallException('Only actions available for SteamServers plugin scripts are : install, uninstall, activate and deactivate.');
        }

        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());

        // En cas d'installation, vérification des dépendances du plugin
        if ($action == 'install') {
            $packetDependencies = $plugin->getPacketDependencies();

            if (!empty($packetDependencies)) {
                $missingPackets = array();

                foreach ($packetDependencies AS $dep) {
                    if (!$sec->isPacketInstalled($dep)) {
                        $missingPackets[] = $dep;
                    }
                }

                if (!empty($missingPackets)) {
                    throw new MissingPacketException($sec, $missingPackets);
                }
            }
        }

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

        $sec->upload($scriptPath, $pluginScript);
        $sec->exec($screenCmd);
    }

    public function getHltvScreenName()
    {
        $screenName = 'hltv-' . $this->getMachine()->getUser() . '-' . $this->getDir();

        return $this->getScreenNameHash($screenName);
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
        return '[DediPanel] ' . $this->getName();
    }

    public function removeFromServer()
    {
        $screenName = $this->getScreenName();
        $scriptPath = $this->getAbsoluteHldsScriptPath();
        $serverPath = $this->getAbsoluteDir();

        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());

        // On commence par vérifier que le serveur n'est pas lancé (sinon on l'arrête)
        $pgrep   = '`ps aux | grep SCREEN | grep "' . $screenName . ' " | grep -v grep | wc -l`';
        $stopCmd = 'if [ ' . $pgrep . ' != "0" ]; then ' . $scriptPath . ' stop; fi; ';
        $sec->exec($stopCmd);

        // Puis on supprime complètement le dossier du serveur
        $delCmd  = 'rm -Rf ' . $serverPath;

        return $sec->exec($delCmd);
    }
    
    public function modifyGameModesCfg()
    {
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        $file = $this->getAbsoluteGameContentDir() . 'gamemodes_server.txt';
        
        $content = $sec->getRemoteFile($file);
        $fileLines = explode("\r\n", $content);
        
        foreach ($fileLines AS &$line) {
            // On ignore la ligne vide et les commentaires
            if (empty($line) || substr($line, 0, 2) == '//') continue;
            
            if (preg_match('#"maxplayers"[ \t]+"[\d]+"#', $line)) {
                $line = preg_replace('#^([ \t]+)"maxplayers"([ \t]+)"[\d]+"(.*)$#', '$1"maxplayers"$2"' . $this->maxplayers . '"$3', $line);
            }
        }
        
        // Upload du nouveau fichier
        return $sec->upload($file, implode("\r\n", $fileLines));
    }
    
    public function getServerLogs()
    {
        $sec = PHPSeclibWrapper::getFromMachineEntity($this->getMachine());
        
        return $sec->getScreenContent($this->getScreenName());
    }
}

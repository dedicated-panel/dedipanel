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

namespace DP\GameServer\GameServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\Core\MachineBundle\Entity\Machine;
use Symfony\Component\Validator\Constraints as Assert;
use DP\GameServer\GameServerBundle\Query\QueryInterface;
use DP\Core\MachineBundle\PHPSeclibWrapper\PHPSeclibWrapper;

/**
 * DP\Core\GameServer\GameServerBundle\Entity\GameServer
 * @ORM\Table(name="gameserver")
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *      "steam" = "DP\GameServer\SteamServerBundle\Entity\SteamServer", 
 *      "minecraft" = "DP\GameServer\MinecraftServerBundle\Entity\MinecraftServer"
 * })
 */
abstract class GameServer
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=32)
     * @Assert\NotBlank(message="gameServer.assert.name")
     */
    protected $name;

    /**
     * @var integer $port
     *
     * @ORM\Column(name="port", type="integer")
     * @Assert\Min(limit="1024", message="gameServer.assert.port")
     * @Assert\Max(limit="65536", message="gameServer.assert.port")
     * @Assert\NotBlank(message="gameServer.assert.port")
     */
    protected $port;

    /**
     * @var integer $installationStatus
     *
     * @ORM\Column(name="installationStatus", type="integer", nullable=true)
     */
    protected $installationStatus;

    /**
     * @var string $dir
     *
     * @ORM\Column(name="dir", type="string", length=64)
     * @Assert\NotBlank(message="gameServer.assert.dir")
     */
    protected $dir;

    /**
     * @var integer $maxplayers
     *
     * @ORM\Column(name="maxplayers", type="integer")
     * @Assert\Min(limit="2", message="gameServer.assert.maxplayers")
     */
    protected $maxplayers;
    
    /**
     * @ORM\ManyToOne(targetEntity="DP\Core\MachineBundle\Entity\Machine", inversedBy="gameServers")
     * @ORM\JoinColumn(name="machineId", referencedColumnName="id")
     * @Assert\NotNull(message="gameServer.assert.machine")
     */
    protected $machine;
    
    /**
     * @ORM\ManyToOne(targetEntity="DP\Core\GameBundle\Entity\Game", inversedBy="gameServers")
     * @ORM\JoinColumn(name="gameId", referencedColumnName="id")
     * @Assert\NotNull(message="gameServer.assert.game")
     */
    protected $game;

    /**
     * @var string $rcon
     *
     * @ORM\Column(name="rconPassword", type="string", length=32, nullable=true)
     */
    protected $rconPassword;
    
    protected $query;
    protected $rcon;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $plugins
     * 
     * @ORM\ManyToMany(targetEntity="DP\Core\GameBundle\Entity\Plugin") 
     * @ORM\JoinTable(name="gameserver_plugins",
     *      joinColumns={@ORM\JoinColumn(name="server_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="plugin_id", referencedColumnName="id")}
     * )
     */
    private $plugins;
    
    
    abstract public function changeStateServer($state);
    
    
    public function __construct()
    {
        $this->plugins = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set machine
     *
     * @param Machine $machine
     */
    public function setMachine(Machine $machine)
    {
        $this->machine = $machine;
    }

    /**
     * Get machine
     *
     * @return Machine
     */
    public function getMachine()
    {
        return $this->machine;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set port
     *
     * @param integer $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Get port
     *
     * @return integer 
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set game
     *
     * @param Game $game
     */
    public function setGame($game)
    {
        $this->game = $game;
    }

    /**
     * Get gameId
     *
     * @return integer 
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * Set installationStatus
     *
     * @param integer $installationStatus
     */
    public function setInstallationStatus($installationStatus)
    {
        $this->installationStatus = $installationStatus;
    }

    /**
     * Get installationStatus
     *
     * @return integer 
     */
    public function getInstallationStatus()
    {
        return $this->installationStatus;
    }

    /**
     * Set dir
     *
     * @param string $dir
     */
    public function setDir($dir)
    {
        $this->dir = trim($dir, '/ ');
    }

    /**
     * Get dir
     *
     * @return string 
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Set maxplayers
     *
     * @param integer $maxplayers
     */
    public function setMaxplayers($maxplayers)
    {
        $this->maxplayers = $maxplayers;
    }

    /**
     * Get maxplayers
     *
     * @return integer 
     */
    public function getMaxplayers()
    {
        return $this->maxplayers;
    }
    
    /**
     * Get absolute path of server installation directory
     * 
     * @return string
     */
    public function getAbsoluteDir()
    {
        return $this->getMachine()->getHome() . '/' . $this->getDir() . '/';
    }
    
    /**
     * Get absolute path of binaries directory
     * 
     * @return string
     */
    protected function getAbsoluteBinDir()
    {        
        return $this->getAbsoluteDir() . $this->getGame()->getBinDir(); 
    }
    
    /**
     * Get absolute path of game content directory
     * 
     * @return string
     */
    protected function getAbsoluteGameContentDir()
    {
        return $this->getAbsoluteBinDir();
    }
    
    protected function getScreenName()
    {
        return sha1($this->getMachine()->getUser() . '-' . $this->getDir(), true);
    }
    
    protected function getInstallScreenName()
    {        
        return sha1($this->getMachine()->getUser() . '-install-' . $this->getDir(), true);
    }
    
    public function getPluginInstallScreenName($scriptName = '')
    {
        return sha1($this->getMachine()->getUser() . '-plugin-install-' . $scriptName . '-' . $this->getDir(), true);
    }
    
    public function setQuery(QueryInterface $query)
    {
        $this->query = $query;
    }
    
    public function getQuery()
    {
        return $this->query;
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
    
    public function setRcon($rcon)
    {
        $this->rcon = $rcon;
        
        return $this->rcon;
    }
    
    public function getRcon()
    {
        return $this->rcon;
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
        
        return utf8_encode(PHPSeclibWrapper::getFromMachineEntity($this->getMachine())
                ->getRemoteFile($path));
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
    
    public function fileExists($filepath)
    {
        $filepath = $this->getAbsoluteGameContentDir() . $filepath;
        
        return $this->getMachine()->fileExists($filepath);
    }
    
    public function dirExists($dirpath)
    {
        $dirpath = $this->getAbsoluteGameContentDir() . $dirpath;
        
        return $this->getMachine()->dirExists($dirpath);
    }
    
    public function remove($path)
    {
        $path = $this->getAbsoluteGameContentDir() . $path;
        
        return PHPSeclibWrapper::getFromMachineEntity($this->getMachine())
                ->remove($path);
    }
    
    public function createDirectory($dirpath)
    {
        $dirpath = $this->getAbsoluteGameContentDir() . $dirpath;
        
        return PHPSeclibWrapper::getFromMachineEntity($this->getMachine())
                ->createDirectory($dirpath);
    }
}
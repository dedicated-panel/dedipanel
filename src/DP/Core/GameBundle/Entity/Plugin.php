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

namespace DP\Core\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\Core\GameBundle\Entity\Game;

/**
 * DP\Core\GameBundle\Entity\Plugin
 * @author Albin Kerouanton 
 *
 * @ORM\Table(name="plugin")
 * @ORM\Entity
 */
class Plugin
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=32)
     */
    private $name = '';

    /**
     * @var string $downloadUrl
     *
     * @ORM\Column(name="downloadUrl", type="string", length=128)
     */
    private $downloadUrl;

    /**
     * @var string $scriptName
     *
     * @ORM\Column(name="scriptName", type="string", length=32)
     */
    private $scriptName;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $games
     * 
     * @ORM\ManyToMany(targetEntity="DP\Core\GameBundle\Entity\Game", mappedBy="plugins")
     * @ORM\JoinTable(
     *      joinColumns={@ORM\JoinColumn(name="plugin_id", referencedColumnName="id")}, 
     *      inverseJoinColumns={@ORM\JoinColumn(name="game_id", referencedColumnName="id")}
     * )
     */
    private $games;
    
    /**
     * @var array $packetDependencies
     * 
     * @ORM\Column(name="packetDependencies", type="array", nullable=true)
     */
    private $packetDependencies;
    
    /**
     * @var string $version
     * 
     * @ORM\Column(name="version", type="string", nullable=true)
     */
    private $version;
    
    public function __construct()
    {
        $this->games = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
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
     * Set downloadUrl
     *
     * @param string $downloadUrl
     */
    public function setDownloadUrl($downloadUrl)
    {
        $this->downloadUrl = $downloadUrl;
        
        return $this;
    }

    /**
     * Get downloadUrl
     *
     * @return string 
     */
    public function getDownloadUrl()
    {
        return $this->downloadUrl;
    }

    /**
     * Set scriptName
     *
     * @param string $scriptName
     */
    public function setScriptName($scriptName)
    {
        $this->scriptName = $scriptName;
        
        return $this;
    }

    /**
     * Get scriptName
     *
     * @return string 
     */
    public function getScriptName()
    {
        return $this->scriptName;
    }

    /**
     * Add game
     *
     * @param Game $game
     */
    public function addGame(Game $game)
    {
        $this->games[] = $game;
        
        if (!$game->getPlugins()->contains($this)) {
            $game->addPlugin($this);
        }
    }
    
    /**
     * Remove game
     * 
     * @param Game $game
     */
    public function removeGame(Game $game)
    {
        $this->games->removeElement($game);
        
        if ($game->getPlugins()->contains($this)) {
            $game->removePlugin($this);
        }
    }
    
    public function setGames(array $games = array())
    {
        $this->games = new \Doctrine\Common\Collections\ArrayCollection($games);
        
        return $this;
    }

    /**
     * Get games
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getGames()
    {
        return $this->games;
    }
    
    public function __toString()
    {
        $name = $this->getName();
        $version = $this->getVersion();
        
        if (!empty($version)) {
            $name .= ' v' . $version;
        }
        
        return $name;
    }
    
    public function setPacketDependencies(array $packetDependencies)
    {
        $this->packetDependencies = $packetDependencies;
        
        return $this;
    }
    
    public function getPacketDependencies()
    {
        return $this->packetDependencies;
    }
    
    /**
     * Sets the plugin version
     * 
     * @param $vesion string Plugin vesion
     * @return Plugin
     */
    public function setVersion($version)
    {
        $this->version = $version;
        
        return $this;
    }
    
    /**
     * Gets the plugin version 
     *
     * @return string Plugin version
     */
    public function getVersion()
    {
        return $this->version;
    }
}

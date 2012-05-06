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

namespace DP\Core\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DP\Core\GameBundle\Entity\Plugin
 * @author Albin Kerouanton 
 *
 * @ORM\Table(name="plugin")
 * @ORM\Entity(repositoryClass="DP\Core\GameBundle\Entity\PluginRepository")
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
    private $name;

    /**
     * @var string $downloadUrl
     *
     * @ORM\Column(name="downloadUrl", type="string", length=128)
     */
    private $downloadUrl;

    /**
     * @var string $archiveType
     *
     * @ORM\Column(name="archiveType", type="string", length=10)
     */
    private $archiveType;

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
     * @ORM\JoinTable(name="games_plugins", 
     *      joinColumns={@ORM\JoinColumn(name="plugin_id", referencedColumnName="id")}, 
     *      inverseJoinColumns={@ORM\JoinColumn(name="game_id", referencedColumnName="id")}
     * )
     */
    private $games;
    
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
     * Set archiveType
     *
     * @param string $archiveType
     */
    public function setArchiveType($archiveType)
    {
        $this->archiveType = $archiveType;
    }

    /**
     * Get archiveType
     *
     * @return string 
     */
    public function getArchiveType()
    {
        return $this->archiveType;
    }

    /**
     * Set scriptName
     *
     * @param string $scriptName
     */
    public function setScriptName($scriptName)
    {
        $this->scriptName = $scriptName;
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
     * @param DP\Core\GameBundle\Entity\Game $game
     */
    public function addGame(\DP\Core\GameBundle\Entity\Game $game)
    {
        $this->games[] = $game;
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
        return $this->getName();
    }
}
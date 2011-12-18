<?php

namespace DP\JeuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DP\JeuBundle\Entity\Jeu
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="DP\JeuBundle\Entity\JeuRepository")
 */
class Jeu
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
     * @ORM\Column(name="name", type="string", length=24)
     */
    private $name;

    /**
     * @var string $installName
     *
     * @ORM\Column(name="installName", type="string", length=24)
     */
    private $installName;

    /**
     * @var string $launchName
     *
     * @ORM\Column(name="launchName", type="string", length=24)
     */
    private $launchName;

    /**
     * @var string $bin
     *
     * @ORM\Column(name="bin", type="string", length=24)
     */
    private $bin;

    /**
     * @var boolean $orangebox
     *
     * @ORM\Column(name="orangebox", type="boolean")
     */
    private $orangebox;

    /**
     * @var string $map
     *
     * @ORM\Column(name="map", type="string", length=20)
     */
    private $map;

    /**
     * @var boolean $available
     *
     * @ORM\Column(name="available", type="boolean")
     */
    private $available;


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
     * Set installName
     *
     * @param string $installName
     */
    public function setInstallName($installName)
    {
        $this->installName = $installName;
    }

    /**
     * Get installName
     *
     * @return string 
     */
    public function getInstallName()
    {
        return $this->installName;
    }

    /**
     * Set launchName
     *
     * @param string $launchName
     */
    public function setLaunchName($launchName)
    {
        $this->launchName = $launchName;
    }

    /**
     * Get launchName
     *
     * @return string 
     */
    public function getLaunchName()
    {
        return $this->launchName;
    }

    /**
     * Set bin
     *
     * @param string $bin
     */
    public function setBin($bin)
    {
        $this->bin = $bin;
    }

    /**
     * Get bin
     *
     * @return string 
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * Set orangebox
     *
     * @param boolean $orangebox
     */
    public function setOrangebox($orangebox)
    {
        $this->orangebox = $orangebox;
    }

    /**
     * Get orangebox
     *
     * @return boolean 
     */
    public function getOrangebox()
    {
        return $this->orangebox;
    }

    /**
     * Set map
     *
     * @param string $map
     */
    public function setMap($map)
    {
        $this->map = $map;
    }

    /**
     * Get map
     *
     * @return string 
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Set available
     *
     * @param boolean $available
     */
    public function setAvailable($available)
    {
        $this->available = $available;
    }

    /**
     * Get available
     *
     * @return boolean 
     */
    public function getAvailable()
    {
        return $this->available;
    }
}
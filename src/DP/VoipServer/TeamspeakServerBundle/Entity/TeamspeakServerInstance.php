<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\VoipServer\TeamspeakServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\VoipServer\TeamspeakServerBundle\ServerQuery\QueryGateway;
use DP\VoipServer\VoipServerBundle\Entity\VoipServerInstance;

/**
 * TeamspeakServerInstance
 *
 * @ORM\Table(name="teamspeak_server_instance")
 * @ORM\Entity(repositoryClass="DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServerInstanceRepository")
 */
class TeamspeakServerInstance extends VoipServerInstance
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $instanceId
     *
     * @ORM\Column(name="instance_id", type="integer")
     */
    protected $instanceId;

    /**
     * @var string $adminToken
     *
     * @ORM\Column(name="admin_token", type="string")
     */
    protected $adminToken;

    /**
     * @var bool $autostart
     *
     * @ORM\Column(name="autostart", type="boolean")
     */
    private $autostart;

    /**
     * @var string $banner
     *
     * @ORM\Column(name="banner", type="text", nullable=true)
     */
    private $banner;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", nullable=true)
     */
    private $password;

    /** @var QueryGateway $query */
    protected $query;


    public function __construct(TeamspeakServer $server)
    {
        $this->autostart = true;

        $this->setServer($server);
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
     * Set the instance id used by the teamspeak server
     * (needed for the query)
     *
     * @param integer $sid
     * @return TeamspeakServerInstance
     */
    public function setInstanceId($sid)
    {
        $this->instanceId = $sid;

        return $this;
    }

    /**
     * Get the instance id used by the teamspeak server
     * (needed for the query)
     *
     * @return integer
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * Set the default admin token
     *
     * @param string $adminToken
     * @return TeamspeakServerInstance
     */
    public function setAdminToken($adminToken)
    {
        $this->adminToken = $adminToken;

        return $this;
    }

    /**
     * Get the default admin token
     *
     * @return string
     */
    public function getAdminToken()
    {
        return $this->adminToken;
    }

    /**
     * Set whether the instance need to autostart or not, when the server start
     *
     * @param boolean $autostart
     * @return TeamspeakServerInstance
     */
    public function setAutostart($autostart)
    {
        $this->autostart = $autostart;

        return $this;
    }

    /**
     * Get whether the instance need to autostart or not, when the server start
     *
     * @return boolean
     */
    public function isAutostart()
    {
        return $this->autostart;
    }

    /**
     * Set the welcome banner
     *
     * @param string $banner
     * @return TeamspeakServerInstance
     */
    public function setBanner($banner)
    {
        $this->banner = $banner;

        return $this;
    }

    /**
     * Get the welcome banner
     *
     * @return string
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * Set the instance password required to join it
     *
     * @param string $password
     * @return TeamspeakServerInstance
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the instance password required to join it
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /** {@inheritdoc} */
    public function installServer(\Twig_Environment $twig)
    {
        $query = $this->getQuery();

        $this->installationStatus = 0;

        $details = $query->createInstance($this);
        if ($details !== false) {
            $this->instanceId = $details['sid'];
            $this->adminToken = strval($details['token']);

            $this->installationStatus = 101;

            return true;
        }

        return false;
    }

    /**
     * Always true as the is no postInstall process
     *
     * {@inheritdoc}
     */
    public function finalizeInstallation(\Twig_Environment $twig)
    {
        return true;
    }

    /**
     * As there is no complex install process,
     * this will return the installation status
     *
     * {@inheritdoc}
     */
    public function getInstallationProgress()
    {
        return $this->installationStatus;
    }

    /** {@inheritdoc} */
    public function changeState($state)
    {
        $sid = $this->instanceId;
        $query = $this->getQuery();

        if ($state == 'start' && $query->isInstanceOffline($sid)) {
            return $query->startInstance($sid);
        }
        elseif ($state == 'stop' && $query->isInstanceOnline($sid)) {
            return $this->getQuery()->stopInstance($sid);
        }
        elseif ($state == 'restart' && $query->isInstanceOnline($sid)) {
            return $this->getQuery()->restartInstance($sid);
        }

        return false;
    }

    /** {@inheritdoc} */
    public function getAbsoluteDir()
    {
        return $this->getServer()->getAbsoluteDir();
    }

    /** {@inheritdoc} */
    public function getType()
    {
        return 'teamspeak';
    }

    /**
     * This is a virtual server so there is no install directory,
     * however the virtual server itself need to be destroyed.
     *
     * @return bool
     */
    public function deleteInstallDir()
    {
        return $this->getQuery()->deleteInstance($this->instanceId);
    }
}

<?php

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

    /** @var QueryGateway $query */
    protected $query;


    public function __construct(TeamspeakServer $server = null)
    {
        $this->server = $server;
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

    public function setInstanceId($sid)
    {
        $this->instanceId = $sid;

        return $this;
    }

    public function getInstanceId()
    {
        return $this->instanceId;
    }

    public function setAdminToken($adminToken)
    {
        $this->adminToken = $adminToken;

        return $this;
    }

    public function getAdminToken()
    {
        return $this->adminToken;
    }

    public function setAutostart($autostart)
    {
        $this->autostart = $autostart;

        return $this;
    }

    public function isAutostart()
    {
        return $this->autostart;
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
     * Always return true
     * As this is a virtual server, there is no install directory to delete
     *
     * @return bool
     */
    public function deleteInstallDir()
    {
        return true;
    }
}

<?php

namespace DP\VoipServer\TeamspeakServerBundle\Entity;

use Dedipanel\PHPSeclibWrapperBundle\Connection\Connection;
use Doctrine\ORM\Mapping as ORM;
use DP\Core\CoreBundle\Exception\DirectoryAlreadyExistsException;
use DP\VoipServer\TeamspeakServerBundle\ServerQuery\QueryGateway;
use DP\VoipServer\TeamspeakServerBundle\Service\ServerQueryFactory;
use DP\VoipServer\VoipServerBundle\Entity\VoipServer;

/**
 * TeamspeakServer
 *
 * @ORM\Table(name="teamspeak_server")
 * @ORM\Entity(repositoryClass="DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServerRepository")
 */
class TeamspeakServer extends VoipServer
{
    /**
     * @var integer $queryPort
     *
     * @ORM\Column(name="query_port", type="integer", nullable=true)
     */
    private $queryPort;

    /**
     * @var string $queryLogin
     *
     * @ORM\Column(name="query_login", type="string", length=32, nullable=true)
     */
    private $queryLogin = 'serveradmin';

    /**
     * @var string $queryPassword
     *
     * @ORM\Column(name="query_passwd", type="string", length=32, nullable=true)
     */
    private $queryPassword;

    /** @var bool $firstStart */
    private $firstStart;


    public function __construct()
    {
        parent::__construct();

        $this->queryPort  = 10011;
        $this->queryLogin = 'serveradmin';
    }

    /**
     * Get the login needed by the query
     *
     * @return string
     */
    public function getQueryLogin()
    {
        return $this->queryLogin;
    }

    /**
     * Set the password needed by the query
     *
     * @param string $password
     * @return TeamspeakServer
     */
    public function setQueryPassword($password)
    {
        $this->queryPassword = $password;

        return $this;
    }

    /**
     * Get the password needed by the query
     *
     * @return string
     */
    public function getQueryPassword()
    {
        return $this->queryPassword;
    }

    /**
     * Set the port needed by the query
     *
     * @param integer $port
     * @return TeamspeakServer
     */
    public function setQueryPort($port)
    {
        $this->queryPort = $port;

        return $this;
    }

    /**
     * Get the port needed by the query
     *
     * @return integer
     */
    public function getQueryPort()
    {
        return $this->queryPort;
    }

    /** {@inheritdoc} */
    public function getInstallationProgress()
    {
        $conn       = $this->getMachine()->getConnection();
        $installDir = $this->getAbsoluteDir();
        $logPath    = $installDir . 'install.log';

        if ($conn->fileExists($installDir . '/ts3server_startscript.sh')) {
            return 100;
        }

        // On récupère les 20 dernières lignes du fichier afin de déterminer le pourcentage
        $installLog = $conn->exec('tail -n 20 ' . $logPath);
        $percent    = $this->getPercentFromInstallLog($installLog);

        return $percent;
    }

    /** {@inheritdoc} */
    public function installServer(\Twig_Environment $twig)
    {
        $conn = $this->getMachine()->getConnection();
        $installDir = $this->getAbsoluteDir();
        $logPath = $installDir . '/install.log';
        $tempPath = $installDir . '/server.tgz';

        if ($conn->dirExists($installDir)) {
            throw new DirectoryAlreadyExistsException("This directory " . $installDir . " already exists.");
        }

        $conn->mkdir($installDir);

        $dlUrl = 'http://dl.4players.de/ts/releases/3.0.10.3/teamspeak3-server_linux-x86-3.0.10.3.tar.gz';
        $untarDir = $installDir . '/teamspeak3-server_linux-x86';
        if ($this->getMachine()->is64Bit()) {
            $dlUrl = 'http://dl.4players.de/ts/releases/3.0.10.3/teamspeak3-server_linux-amd64-3.0.10.3.tar.gz';
            $untarDir = $installDir . '/teamspeak3-server_linux-amd64';
        }

        $cmd  = 'wget -o ' . $logPath . ' -O ' . $tempPath . ' ' . $dlUrl . ' ';
        $cmd .= '&& tar zxf ' . $tempPath . ' -C ' . $installDir . ' ';
        $cmd .= '&& mv ' . $untarDir . '/* ' . $installDir . ' ';
        $cmd .= '&& wget -O ' . $installDir . '/sql/defaults.sql http://media.teamspeak.com/literature/defaults.sql ';
        $cmd .= '&& rm -Rf ' . $untarDir . ' ' . $tempPath . ' ' . $logPath;

        $conn->exec($cmd);

        $this->installationStatus = 0;

        return true;
    }

    /** {@inheritdoc} */
    public function finalizeInstallation(\Twig_Environment $twig)
    {
        $conn = $this->getMachine()->getConnection();
        $installDir = $this->getAbsoluteDir();

        $conn->exec("echo \$SSH_CLIENT | awk '{print \$1}' >> ${installDir}/query_ip_whitelist.txt");

        $this->firstStart = true;
        $this->changeState('start');
        sleep(2);
        $this->changeState('stop');

        $this->firstStart = false;
        $this->changeState('start');

        $this->installationStatus = 101;
    }

    /** {@inheritdoc} */
    public function changeState($state)
    {
        $params = '';

        if ($state == 'start') {
            $params = $this->getStartParams();
        }

        return $this
            ->getMachine()
            ->getConnection()
            ->exec($this->getAbsoluteDir() . '/ts3server_startscript.sh ' . $state . ' ' . $params)
        ;
    }

    public function getStartParams()
    {
        $params[] = 'logquerycommands=0';
        $params[] = 'create_default_virtualserver=0';

        if ($this->firstStart) {
            $params[] = 'serveradmin_password=' . $this->queryPassword;
        }

        return '"' . implode(' ', $params) . '"' . (($this->firstStart) ? ' &' : '');
    }

    /** {@inheritdoc} */
    protected function getAbsoluteDir()
    {
        return rtrim($this->getMachine()->getHome(), '/') . '/teamspeak';
    }

    /** {@inheritdoc} */
    public function getName()
    {
        return strval($this);
    }

    public function getFullName()
    {
        return $this->getName();
    }

    /** {@inheritdoc} */
    public function getType()
    {
        return 'teamspeak';
    }

    public function __toString()
    {
        return strval($this->getMachine());
    }
}

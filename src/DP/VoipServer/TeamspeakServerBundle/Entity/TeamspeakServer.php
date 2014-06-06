<?php

namespace DP\VoipServer\TeamspeakServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DP\Core\CoreBundle\Exception\DirectoryAlreadyExistsException;
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
    private $queryPort = 10011;

    /**
     * @var string $queryLogin
     *
     * @ORM\Column(name="query_login", type="string", length=32, nullable=true)
     */
    private $queryLogin;
    /**
     * @var string $queryPassword
     *
     * @ORM\Column(name="query_passwd", type="string", length=32, nullable=true)
     */
    private $queryPassword;

    /**
     * @var string $adminToken
     *
     * @ORM\Column(name="admin_token", type="string", length=40, nullable=true)
     */
    private $adminToken;


    public function __construct()
    {
        parent::__construct();

        $this->queryPort = 10011;
    }

    public function setQueryLogin($login)
    {
        $this->queryLogin = $login;

        return $this;
    }

    public function getQueryLogin()
    {
        return $this->queryLogin;
    }

    public function setQueryPassword($password)
    {
        $this->queryPassword = $password;

        return $this;
    }

    public function getQueryPassword()
    {
        return $this->queryPassword;
    }

    public function setQueryPort($port)
    {
        $this->queryPort = $port;

        return $this;
    }

    public function getQueryPort()
    {
        return $this->queryPort;
    }

    public function setAdminToken($token)
    {
        $this->adminToken = $token;

        return $this;
    }

    public function getAdminToken()
    {
        return $this->adminToken;
    }

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
        $cmd .= '&& rm -Rf ' . $untarDir . ' ' . $tempPath . ' ' . $logPath . ' &';

        $conn->exec($cmd);

        $this->installationStatus = 0;
    }

    protected function getAbsoluteDir()
    {
        return rtrim($this->getMachine()->getHome(), '/') . '/teamspeak';
    }

    public function changeState($state)
    {
        return $this
            ->getMachine()
            ->getConnection()
            ->exec($this->getAbsoluteDir() . '/ts3server_startscript.sh ' . $state)
        ;
    }

    public function finalizeInstallation(\Twig_Environment $twig)
    {
        $screenName = 'dp-ts-first-start';
        $installDir = $this->getAbsoluteDir();
        $conn = $this->getMachine()->getConnection();

        $conn->exec('screen -dmS ' . $screenName . ' ' . $installDir . '/ts3server_minimal_runscript.sh start');

        sleep(2); // Oh shit !!
        $content = explode("\n", $conn->getScreenContent($screenName));

        foreach ($content AS $line) {
            $matches = array();

            if (preg_match('#loginname= "(.*)", password= "(.*)"#', $line, $matches)) {
                $this->queryLogin    = $matches[1];
                $this->queryPassword = $matches[2];
            }
            elseif (preg_match('#token=(.*)#', $line, $matches)) {
                $this->adminToken = $matches[1];
            }
        }

        $conn->exec('kill `screen -ls | grep \'' . $screenName . '\' | awk -F \'.\' \'{print $1}\'`');

        $conn->exec('echo $SSH_CLIENT | awk \'{print $1}\' >> ' . $installDir . '/query_ip_whitelist.txt');

        $this->installationStatus = 101;
    }

    public function getType()
    {
        return 'teamspeak';
    }
}

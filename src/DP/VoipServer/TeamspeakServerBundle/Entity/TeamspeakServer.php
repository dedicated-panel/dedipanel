<?php

namespace DP\VoipServer\TeamspeakServerBundle\Entity;

use Dedipanel\PHPSeclibWrapperBundle\Connection\Connection;
use Doctrine\ORM\Mapping as ORM;
use DP\VoipServer\TeamspeakServerBundle\Service\ServerQueryFactory;
use DP\VoipServer\VoipServerBundle\Entity\VoipServer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

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
    private $queryLogin;

    /**
     * @var string $queryPassword
     *
     * @ORM\Column(name="query_passwd", type="string", length=32, nullable=true)
     */
    private $queryPassword;

    /**
     * @var integer $filetransferPort
     *
     * @ORM\Column(name="filetransfer_port", type="integer", nullable=true)
     */
    private $filetransferPort;

    /**
     * @var integer $voicePort
     *
     * @ORM\Column(name="voice_port", type="integer", nullable=true)
     */
    private $voicePort;

    /**
     * @var UploadedFile $licenceFile
     */
    private $licenceFile;

    /** @var bool $firstStart */
    private $firstStart;


    public function __construct()
    {
        parent::__construct();

        $this->voicePort = 9987;
        $this->queryPort  = 10011;
        $this->queryLogin = 'serveradmin';
        $this->filetransferPort = 30033;
    }

    /**
     * Set the port needed by the query
     *
     * @param integer $port
     * @return integer
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
        // Prevent unsetting the password from forms
        if (!empty($password)) {
            $this->queryPassword = $password;
        }

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
     * Set the filetransfer port used by teamspeak
     *
     * @param integer $filetransferPort
     * @return TeamspeakServer
     */
    public function setFiletransferPort($filetransferPort)
    {
        $this->filetransferPort = $filetransferPort;

        return $this;
    }

    /**
     * Get the filetransfer port used by teamspeak
     *
     * @return integer
     */
    public function getFiletransferPort()
    {
        return $this->filetransferPort;
    }

    /**
     * Get the default voice port
     * (will be used by the first instance)
     *
     * @param integer $voicePort
     * @return TeamspeakServer
     */
    public function setVoicePort($voicePort)
    {
        $this->voicePort = $voicePort;

        return $this;
    }

    /**
     * Get the default voice port
     * (will be used by the first instance)
     *
     * @return integer
     */
    public function getVoicePort()
    {
        return $this->voicePort;
    }

    /**
     * Set the uploaded licence file
     *
     * @param UploadedFile $licenceFile
     * @return TeamspeakServer
     */
    public function setLicenceFile(UploadedFile $licenceFile = null)
    {
        $this->licenceFile = $licenceFile;

        return $this;
    }

    /**
     * Has the licence file been uploaded ?
     *
     * @return boolean
     */
    public function hasLicenceFile()
    {
        return $this->licenceFile !== null;
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

        if (!$conn->dirExists($installDir)) {
            return false;
        }

        $this->uploadConfigFile();

        if ($this->hasLicenceFile()) {
            $this->uploadLicenceFile();
        }

        $conn->exec("echo \$SSH_CLIENT | awk '{print \$1}' >> ${installDir}/query_ip_whitelist.txt");

        $this->firstStart = true;
        $this->changeState('start');
        sleep(2);
        $this->changeState('stop');

        $this->firstStart = false;
        $this->changeState('start');

        $this->installationStatus = 101;

        return true;
    }

    public function uploadConfigFile()
    {
        $config  = '';
        $config .= $this->getGeneralConfig() . "\n";
        $config .= $this->getDatabaseConfig();

        $filepath = $this->getAbsoluteDir() . '/ts3server.ini';

        return $this
            ->getMachine()
            ->getConnection()
            ->upload($filepath, $config, 0750)
        ;
    }

    public function uploadLicenceFile()
    {
        if (!$this->hasLicenceFile()) {
            return false;
        }

        $licencePath = $this->getAbsoluteDir() . '/serverkey.dat';
        $filepath = $this->licenceFile->getPathname();
        $content = file_get_contents($filepath);

        @unlink($filepath);
        unset($this->licenceFile);

        return $this
            ->getMachine()
            ->getConnection()
            ->upload($licencePath, $content, 0750)
        ;
    }

    public function getGeneralConfig()
    {
        $publicIp  = $this->getMachine()->getPublicIp();
        $privateIp = $this->getMachine()->getPrivateIp();

        return <<<EOF
machine_id=
default_voice_port={$this->voicePort}
voice_ip=${publicIp}
licensepath=
filetransfer_port={$this->filetransferPort}
filetransfer_ip={$publicIp}
query_port={$this->queryPort}
query_ip={$privateIp}
logpath=logs/
logquerycommands=0
EOF;
    }

    public function getDatabaseConfig()
    {
        return <<<EOF
dbplugin=ts3db_sqlite3
dbpluginparameter=
dbsqlpath=sql/
dbsqlcreatepath=create_sqlite/
EOF;

    }

    /** {@inheritdoc} */
    public function changeState($state)
    {
        $cmd = $this->getAbsoluteDir() . '/ts3server_startscript.sh ' . $state;

        if ($state == 'start') {
            $cmd .= ' ' . $this->getStartParams();
        }

        $core = $this->getCore();
        if (!empty($core)) {
            $cmd = 'taskset -c ' . implode(',', $core) . ' ' . $cmd;
        }

        return $this
            ->getMachine()
            ->getConnection()
            ->exec($cmd)
        ;
    }

    public function getStartParams()
    {
        $params = array();
        $params[] = 'create_default_virtualserver=0';

        if ($this->firstStart) {
            $password = \TeamSpeak3_Helper_String::factory($this->queryPassword)->escape();

            $params[] = 'serveradmin_password=' . $this->queryPassword;
        }

        return '"' . implode(' ', $params) . '"' . (($this->firstStart) ? ' &' : '');
    }

    /** {@inheritdoc} */
    public function getAbsoluteDir()
    {
        return rtrim($this->getMachine()->getHome(), '/') . '/' . trim($this->dir, '/');
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

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('voicePort', new Assert\NotBlank(array('message' => 'teamspeak_server.assert.voice_port.empty')));
        $metadata->addPropertyConstraint('voicePort', new Assert\Range(array(
            'min' => 1024,
            'minMessage' => 'teamspeak_server.assert.voice_port.min',
            'max' => 65536,
            'maxMessage' => 'teamspeak_server.assert.voice_port.max',
        )));
        $metadata->addPropertyConstraint('queryPort', new Assert\NotBlank(array('message' => 'teamspeak_server.assert.query_port.empty')));
        $metadata->addPropertyConstraint('queryPort', new Assert\Range(array(
            'min' => 1024,
            'minMessage' => 'teamspeak_server.assert.query_port.min',
            'max' => 65536,
            'maxMessage' => 'teamspeak_server.assert.query_port.max',
        )));
        $metadata->addPropertyConstraint('queryPassword', new Assert\NotBlank(array('message' => 'teamspeak_server.assert.query_password.empty')));
        $metadata->addPropertyConstraint('filetransferPort', new Assert\NotBlank(array('message' => 'teamspeak_server.assert.filetransfer_port.empty')));
        $metadata->addPropertyConstraint('filetransferPort', new Assert\Range(array(
            'min' => 1024,
            'minMessage' => 'teamspeak_server.assert.filetransfer_port.min',
            'max' => 65536,
            'maxMessage' => 'teamspeak_server.assert.filetransfer_port.max',
        )));
        $metadata->addPropertyConstraint('licenceFile', new Assert\File(array(
            'maxSize' => 200,
            'maxSizeMessage' => 'teamspeak_server.assert.licence_file.max_size',
        )));
    }
}

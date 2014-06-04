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

        // var_dump($installDir, $conn->dirExists($installDir));
        // exit();

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

    private function getAbsoluteDir()
    {
        return rtrim($this->getMachine()->getHome(), '/') . '/teamspeak';
    }
}

<?php

namespace DP\Core\CoreBundle\Model;

use Doctrine\ORM\Mapping as ORM;

abstract class AbstractServer implements ServerInterface
{
    /**
     * @var integer $installationStatus
     *
     * @ORM\Column(name="installation_status", type="integer", nullable=true)
     */
    protected $installationStatus;

    /** @var boolean $alreadyInstalled Used by create process **/
    protected $alreadyInstalled;


    /**
     * @return string Absolute path of the installation directory
     */
    abstract protected function getAbsoluteDir();

    /** {@inheritdoc} */
    abstract public function getName();

    /**
     * Set whether is already already installed
     *
     * @param boolean $alreadyInstalled
     *
     * @return GameServer
     */
    public function setAlreadyInstalled($alreadyInstalled)
    {
        $this->alreadyInstalled = $alreadyInstalled;

        if ($alreadyInstalled) {
            $this->installationStatus = 100;
        }

        return $this;
    }

    /**
     * Is already installed ? (from form)
     *
     * @return boolean
     */
    public function isAlreadyInstalled()
    {
        return $this->alreadyInstalled || $this->isInstallationEnded();
    }

    /**
     * {@inheritdoc}
     */
    public function setInstallationStatus($installationStatus)
    {
        $this->installationStatus = $installationStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallationStatus()
    {
        return $this->installationStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function isInstallationEnded()
    {
        return $this->installationStatus >= 101;
    }

    /**
     * {@inheritdoc}
     */
    public function getMachine()
    {
        return $this->machine;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteServer()
    {
        $this->changeState('stop');

        return $this->deleteInstallDir();
    }

    public function deleteInstallDir()
    {
        $conn = $this->getMachine()->getConnection();
        $installDir = $this->getAbsoluteDir();

        if ($conn->dirExists($installDir)) {
            return (bool) $conn->getSFTP()->delete($installDir);
        }

        return true;
    }

    /**
     * Try to retrieve the most recent percentage in $installLog
     *
     * @param string $installLog Will seperate log by lines
     * @return null|string
     */
    protected function getPercentFromInstallLog($installLog)
    {
        // On recherche dans chaque ligne en commencant par la fin
        // Un signe "%" afin de connaître le % le plus à jour
        $lines = array_reverse(explode("\n", $installLog));

        foreach ($lines AS $line) {
            $percentPos = strpos($line, '%');

            if ($percentPos !== false) {
                $line = substr($line, 0, $percentPos);
                $spacePos = strrpos($line, ' ')+1;

                return substr($line, $spacePos);
            }
        }

        return null;
    }

    /** {@inheritdoc} */
    public function getFullName()
    {
        return '[DediPanel] ' . $this->getName();
    }
}

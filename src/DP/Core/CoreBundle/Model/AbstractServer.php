<?php

namespace DP\Core\CoreBundle\Model;

abstract class AbstractServer implements ServerInterface
{
    /**
     * @var integer $installationStatus
     */
    protected $installationStatus;


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

    public function deleteServer()
    {
        $conn = $this->getMachine()->getConnection();
        $installDir = $this->getAbsoluteDir();

        $this->changeState('stop');

        if ($conn->dirExists($installDir)) {
            return $conn->getSFTP()->delete($installDir);
        }

        return true;
    }

    abstract protected function getAbsoluteDir();
}

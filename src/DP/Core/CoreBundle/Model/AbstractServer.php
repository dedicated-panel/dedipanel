<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractServer implements ServerInterface
{
    /**
     * @var integer $installationStatus
     *
     * @ORM\Column(name="installation_status", type="integer", nullable=true)
     */
    protected $installationStatus;

    /**
     * @var boolean $alreadyInstalled Used by the creation process
     */
    protected $alreadyInstalled;

    /**
     * @var array $core
     *
     * @ORM\Column(name="core", type="simple_array", nullable=true)
     */
    protected $core = array();

    /**
     * @var string $dir
     *
     * @ORM\Column(name="dir", type="string", length=64)
     */
    protected $dir;


    /** {@inheritdoc} */
    abstract public function getName();

    /**
     * Set whether is already already installed
     *
     * @param boolean $alreadyInstalled
     *
     * @return AbstractServer
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

    /** 
     * @var array $core
     * @return array
     */
    public function setCore(array $core = array())
    {
        $this->core = $core;

        return $core;
    }

    /** {@inheritdoc} */
    public function getCore()
    {
        return $this->core;
    }

    /** {@inheritdoc} */
    public function setDir($dir)
    {
        $this->dir = trim($dir, '/ ');
    }

    /** {@inheritdoc} */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Get absolute path of server installation directory
     *
     * @return string
     */
    public function getAbsoluteDir()
    {
        return rtrim($this->getMachine()->getHome(), '/') . '/' . $this->getDir() . '/';
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new Assert\Callback('validateServer'));
    }

    public function validateServer(ExecutionContextInterface $context)
    {
        if ($this->getMachine() !== null && $this->getMachine()->getConnection() !== null) {
            $relDir = $this->getDir();
            $absDir = $this->getAbsoluteDir();

            if (!$this->getMachine()->getConnection()->testSSHConnection()) {
                $context->buildViolation('gameServer.assert.machine_unavailable')
                    ->atPath('machine')
                    ->addViolation();
            }
            elseif (!$this->isAlreadyInstalled() && !empty($relDir)
            && $this->getMachine()->getConnection()->dirExists($absDir)) {
                $context->buildViolation('gameServer.assert.directory_exists')
                    ->atPath('dir')
                    ->addViolation();
            }
            elseif ($this->isAlreadyInstalled() && !empty($relDir)
            && !$this->getMachine()->getConnection()->dirExists($absDir)) {
                $context->buildViolation('gameServer.assert.directory_not_exists')
                    ->atPath('dir')
                    ->addViolation();
            }
        }
    }
}

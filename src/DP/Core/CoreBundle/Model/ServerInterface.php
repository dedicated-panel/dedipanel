<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Model;

interface ServerInterface
{
    const ACTION_START = 'start';
    const ACTION_STOP  = 'stop';
    const ACTION_RESTART = 'restart';

    /**
     * Is the server installation ended ?
     *
     * @return boolean
     */
    public function isInstallationEnded();

    /**
     * Is the server already installed on the machine,
     * before the panel done anything ?
     *
     * @return boolean
     */
    public function isAlreadyInstalled();

    /**
     * Fetch the installation progress
     *
     * @return integer
     */
    public function getInstallationProgress();

    /**
     * Set the installation status in database
     *
     * @param integer $status
     * @return ServerInterface
     */
    public function setInstallationStatus($status);

    /**
     * Get the installation status in database
     *
     * @return integer
     */
    public function getInstallationStatus();

    /**
     * Launch the installation process
     *
     * @param \Twig_Environment $twig
     * @throws DP\Core\CoreBundle\Exception\MissingPacketException
     * @throws DP\Core\CoreBundle\Exception\InstallAlreadyStartedException
     * @throws Dedipanel\PHPSeclibWrapperBundle\Connection\Exception\ConnectionErrorException
     */
    public function installServer(\Twig_Environment $twig);

    /**
     * Finalize the installation process
     *
     * @param \Twig_Environment $twig
     */
    public function finalizeInstallation(\Twig_Environment $twig);

    /**
     * Destroy the server
     *
     * @throws Dedipanel\PHPSeclibWrapperBundle\Connection\Exception\ConnectionErrorException
     * @return boolean
     */
    public function deleteServer();

    /**
     * Change the server state (one of ACTION_*)
     *
     * @return string
     */
    public function changeState($state);

    /**
     * Get the machine on which the server is installed (or need to be installed)
     *
     * @return DP\Core\MachineBundle\Entity\Machine
     */
    public function getMachine();

    /**
     * Get the server name
     *
     * @return string
     */
    public function getFullName();

    /**
     * Set the core(s) used by the server
     *
     * @param array $core
     * @return ServerInterface
     */
    public function setCore(array $core = array());

    /**
     * Get the core(s) used by the server
     *
     * @return array
     */
    public function getCore();

    /**
     * Set dir relative to user home
     *
     * @param string $dir
     * @return null|\DP\VoipServer\VoipServerBundle\Entity\VoipServer
     */
    public function setDir($dir);

    /**
     * Get dir relative to user home
     *
     * @return string
     */
    public function getDir();

    /**
     * Get absolute dir
     *
     * @return string Absolute path of the installation directory
     */
    public function getAbsoluteDir();
}

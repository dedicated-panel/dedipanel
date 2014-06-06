<?php

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
     * @return integer
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
     */
    public function deleteServer();

    /**
     * Change the server state (one of ACTION_*)
     *
     * @return boolean
     */
    public function changeState($state);

    /**
     * Get the machine on which the server is installed (or need to be installed)
     *
     * @return DP\Core\MachineBundle\Entity\Machine
     */
    public function getMachine();
}

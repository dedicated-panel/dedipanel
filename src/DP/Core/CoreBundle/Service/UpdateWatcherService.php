<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Service;

class UpdateWatcherService
{
    private $currentVersion;
    private $updateAvailable = false;
    private $versionAvailable;
    private $versionFile;

    public function __construct($currentVersion, $watcherDir)
    {
        $this->currentVersion = $currentVersion;
        $this->versionFile = $watcherDir . '/version.json';

        $this->process();
    }

    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    public function isUpdateAvailable()
    {
        return $this->updateAvailable;
    }

    public function getAvailableVersion()
    {
        return $this->versionAvailable;
    }

    private function process()
    {
        $version = '';

        if (!file_exists($this->versionFile)
        || filemtime($this->versionFile) <= mktime(0, 0, 0)) {
            $version = $this->fetchData();
        }

        if (empty($version)) {
            $version = @file_get_contents($this->versionFile);
        }

        if ($version !== false) {
            $this->processVersion($version);
        }
    }
    
    private function fetchData()
    {
        $context = stream_context_create(array(
            'http' => array(
                'method'  => 'GET',
                'timeout' => 1, 
            )
        ));

        $version = @file_get_contents('http://www.dedicated-panel.net/version.json', false, $context);

        if ($version !== false) {
            file_put_contents($this->versionFile, $version);
        }

        return $version;
    }

    private function processVersion($version)
    {
        $version = json_decode($version);

        $this->versionAvailable = $version->version;
        $this->updateAvailable  = false;

        if (version_compare($this->versionAvailable, $this->currentVersion) == 1) {
            $this->updateAvailable = true;
        }
    }
}

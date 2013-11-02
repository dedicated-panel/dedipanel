<?php

namespace DP\Core\UserBundle\Service;

class UpdateWatcherService
{
    protected $currentVersion;
    protected $updateAvailable;
    protected $versionAvailable;
    
    public function __construct($currentVersion)
    {
        $this->currentVersion = $currentVersion;
        
        $this->fetchData();
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
    
    protected function fetchData()
    {
        $context = stream_context_create(array(
            'http' => array(
                'timeout' => 5, 
            )
        ));
        
        $version = @file_get_contents('http://www.dedicated-panel.net/version.json', false, $context);
        
        if (strlen($version) > 0) {
            $version = json_decode($version);
            $this->versionAvailable = $version->version;
            
            if (version_compare($this->versionAvailable, $this->currentVersion) == 1) {
                $this->updateAvailable = true;
            }
            else {
                $this->updateAvailable = false;
            }
        }
        
        return $this->updateAvailable;
    }
}

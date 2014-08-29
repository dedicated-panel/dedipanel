<?php

namespace DP\Core\CoreBundle\Extension;

use DP\Core\CoreBundle\Service\UpdateWatcherService;

class UpdateWatcherExtension extends \Twig_Extension
{
    protected $updateWatcher;
    
    public function __construct(UpdateWatcherService $updateWatcher)
    {
        $this->updateWatcher = $updateWatcher;
    }
    
    public function getGlobals()
    {
        return array(
            'dedipanel' => array(
                'current_version'   => $this->updateWatcher->getCurrentVersion(), 
                'update_available'  => $this->updateWatcher->isUpdateAvailable(), 
                'version_available' => $this->updateWatcher->getAvailableVersion(), 
            )
        );
    }
    
    public function getName()
    {
        return 'dedipanel_update_watcher';
    }
}

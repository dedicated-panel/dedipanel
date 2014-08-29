<?php

namespace DP\Core\CoreBundle\Twig;

use DP\Core\CoreBundle\Service\UpdateWatcherService;

class CoreExtension extends \Twig_Extension
{
    /**
     * @var \DP\Core\CoreBundle\Service\UpdateWatcherService
     */
    private $updateWatcher;

    /**
     * @var boolean
     */
    private $debug;
    
    public function __construct(UpdateWatcherService $updateWatcher, $debug)
    {
        $this->updateWatcher = $updateWatcher;
        $this->debug         = $debug;
    }
    
    public function getGlobals()
    {
        return [
            'dedipanel' => [
                'debug' => $this->debug,
                'current_version'   => $this->updateWatcher->getCurrentVersion(),
                'update_available'  => $this->updateWatcher->isUpdateAvailable(),
                'version_available' => $this->updateWatcher->getAvailableVersion(),
            ],
        ];
    }
    
    public function getName()
    {
        return 'dedipanel_core_extension';
    }
}

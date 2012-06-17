<?php

namespace DP\Core\DistributionBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use DP\Core\DistributionBundle\Configurator\Step\UserStep;
use DP\Core\DistributionBundle\Configurator\Step\FixturesStep;

class DPDistributionBundle extends Bundle
{
    public function getParent()
    {
        return 'SensioDistributionBundle';
    }
    
    public function boot()
    {
        $configurator = $this->container->get('sensio.distribution.webconfigurator');
        $usrMgr = $this->container->get('fos_user.user_manager');
        
        $configurator->addStep(new Configurator\Step\FixturesStep(array('container' => $this->container)));
        $configurator->addStep(new UserStep(array('usrMgr' => $usrMgr)));
    }
}

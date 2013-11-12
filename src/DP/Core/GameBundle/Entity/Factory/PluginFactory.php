<?php

namespace DP\Core\GameBundle\Entity\Factory;

use DP\Admin\AdminBundle\Entity\Factory\FactoryInterface;
use DP\Core\GameBundle\Entity\Plugin;

class PluginFactory implements FactoryInterface
{
    public function createEntity()
    {
        return new Plugin();
    }
}

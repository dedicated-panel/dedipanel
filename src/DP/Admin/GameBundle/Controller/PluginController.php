<?php

namespace DP\Admin\GameBundle\Controller;

use DP\Admin\AdminBundle\Controller\CRUDController;

class PluginController extends CRUDController
{
    public function getDescriptor()
    {
        return $this->get('dp.descriptor.plugin_admin');
    }
}

<?php

namespace DP\Admin\UserBundle\Controller;

use DP\Admin\AdminBundle\Controller\CRUDController;

class GroupController extends CRUDController
{
    public function getDescriptor()
    {
        return $this->get('dp.descriptor.group_admin');
    }
}

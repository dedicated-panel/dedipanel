<?php

namespace DP\Core\UserBundle\Entity\Factory;

use DP\Admin\AdminBundle\Entity\Factory\FactoryInterface;
use DP\Core\UserBundle\Entity\Group;

class GroupFactory implements FactoryInterface
{
    public function createEntity()
    {
        return new Group('');
    }
}

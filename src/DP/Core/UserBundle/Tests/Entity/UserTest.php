<?php

namespace DP\Core\UserBundle\Tests\Entity;

use FOS\UserBundle\Tests\Model\UserTest as BaseUserTest;

class UserTest extends BaseUserTest
{
    protected function getClass()
    {
        return $this->getMock('DP\Core\UserBundle\Entity\User');
    }
}

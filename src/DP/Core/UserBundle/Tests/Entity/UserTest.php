<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\UserBundle\Tests\Entity;

use FOS\UserBundle\Tests\Model\UserTest as BaseUserTest;

class UserTest extends BaseUserTest
{
    protected function getClass()
    {
        return $this->getMock('DP\Core\UserBundle\Entity\User');
    }
}

<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\UserBundle\Tests\Security;

use DP\Core\UserBundle\Security\UserObjectVoter;
use DP\Core\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class UserObjectVoterTest extends \PHPUnit_Framework_TestCase
{
    const SAME = 'same';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $groupRepo;

    /** @var \DP\Core\UserBundle\Security\UserObjectVoter */
    private $voter;

    /** @var Symfony\Component\Security\Core\Authentication\TokenInterface */
    private $token;

    public function setUp()
    {
        $this->groupRepo = $this->getMockBuilder('DP\Core\UserBundle\Entity\GroupRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->voter = new UserObjectVoter($this->groupRepo);

        $this->token = $this->getMock('Symfony\Component\Security\Core\Authentication\TokenInterface');
    }

    public function testSupportsClass()
    {
        $this->assertTrue($this->voter->supportsClass(get_class(new User)));
        $this->assertFalse($this->voter->supportsClass('Foo'));
    }

    public function testSupportsAttribute()
    {
        $this->assertTrue($this->voter->supportsAttribute('ROLE_DP_TEST'));
        $this->assertFalse($this->voter->supportsAttribute('ROLE_FOO_TEST'));
    }

    public function testOnSameObject()
    {
        $user  = $this->getMock('DP\Core\UserBundle\Entity\User');
        $group = $this->getMock('DP\Core\UserBundle\Entity\Group');
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $user->expects($this->atLeastOnce()) // called 3 times per call to vote() method
            ->method('isSuperAdmin')
            ->will($this->returnValue(false));
        $user->expects($this->atLeastOnce()) // called 3 times per call to vote() method
            ->method('getGroup')
            ->will($this->returnValue($group));
        $token->expects($this->atLeastOnce())
            ->method('getUser')
            ->will($this->returnValue($user));
        $this->groupRepo->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue([$group]));

        // Can view is own profile
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, $user, ['ROLE_DP_ADMIN_USER_SHOW']));

        // Can not update/delete himself if not super admin
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, $user, ['ROLE_DP_ADMIN_USER_UPDATE']));
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, $user, ['ROLE_DP_ADMIN_USER_DELETE']));
    }

    public function testOnSameObjectWhenSuperAdmin()
    {
        $user  = $this->getMock('DP\Core\UserBundle\Entity\User');
        $group = $this->getMock('DP\Core\UserBundle\Entity\Group');
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $user->expects($this->atLeastOnce()) // called 3 times per call to vote() method
            ->method('isSuperAdmin')
            ->will($this->returnValue(true));
        $user->expects($this->atLeastOnce()) // called 3 times per call to vote() method
            ->method('getGroup')
            ->will($this->returnValue($group));
        $token->expects($this->atLeastOnce())
            ->method('getUser')
            ->will($this->returnValue($user));
        $this->groupRepo->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue([$group]));

        // Can update/delete himself if super admin
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, $user, ['ROLE_DP_ADMIN_USER_UPDATE']));
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, $user, ['ROLE_DP_ADMIN_USER_DELETE']));
    }

    public function testOnOtherObject()
    {
        $user  = $this->getMock('DP\Core\UserBundle\Entity\User');
        $other = $this->getMock('DP\Core\UserBundle\Entity\User');
        $group = $this->getMock('DP\Core\UserBundle\Entity\Group');
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $user->expects($this->any())
            ->method('isSuperAdmin')
            ->will($this->returnValue(false));
        $user->expects($this->any())
            ->method('getGroup')
            ->will($this->returnValue($group));
        $other->expects($this->any())
            ->method('getGroup')
            ->will($this->returnValue($group));
        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));
        $this->groupRepo->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue([$group]));

        // Can view, update, delete profile of other users
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, $other, ['ROLE_DP_ADMIN_USER_SHOW']));
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, $other, ['ROLE_DP_ADMIN_USER_UPDATE']));
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, $other, ['ROLE_DP_ADMIN_USER_DELETE']));
    }
}

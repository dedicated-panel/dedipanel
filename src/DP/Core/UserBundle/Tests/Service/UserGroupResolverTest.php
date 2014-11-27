<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\UserBundle\Tests\Service;

use DP\Core\UserBundle\Entity\User;
use DP\Core\UserBundle\Service\UserGroupResolver;

class UserGroupResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $groupRepo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $user;

    /**
     * @var UserGroupResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->groupRepo = $this->getMockBuilder('DP\Core\UserBundle\Entity\GroupRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('getChildren'))
            ->getMock();

        $this->user = $this->getMock('DP\Core\UserBundle\Entity\User');
        /** @var \PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->user));

        $this->context = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->context
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token));

        $this->resolver = new UserGroupResolver($this->groupRepo, $this->context);
    }

    public function testGettingAccessibleGroupsWhenSuperAdmin()
    {
        $groups = ['1', '1-1', '2', '3'];

        $this->context->expects($this->once())
            ->method('isGranted')
            ->will($this->returnValue(true));
        $this->groupRepo->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue($groups));
        $this->user->expects($this->never())
            ->method('getGroup');

        $this->assertEquals($groups, $this->resolver->getAccessibleGroups());
    }

    public function testGettingAccessibleGroupsWhenAdmin()
    {
        $groups = ['1', '1-1'];

        $this->context->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValueMap([
                [User::ROLE_SUPER_ADMIN, null, false],
                [User::ROLE_ADMIN, null, true],
            ]));
        $this->groupRepo->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue($groups));
        $this->user->expects($this->any())
            ->method('getGroup')
            ->will($this->returnValue('1'));

        $this->assertEquals($groups, $this->resolver->getAccessibleGroups());
    }

    public function testGettingAccessibleGroupsWhenNormalUser()
    {
        $groups = ['1'];

        $this->context->expects($this->exactly(2))
            ->method('isGranted')
            ->will($this->returnValueMap([
                [User::ROLE_SUPER_ADMIN, false],
                [User::ROLE_ADMIN, false],
            ]));
        $this->groupRepo->expects($this->never())
            ->method('getChildren');
        $this->user->expects($this->exactly(2))
            ->method('getGroup')
            ->will($this->returnValue('1'));

        $this->assertEquals($groups, $this->resolver->getAccessibleGroups());
    }

    public function testGettingAccessibleGroupsWhenErroredUser()
    {
        $this->context->expects($this->exactly(2))
            ->method('isGranted')
            ->will($this->returnValue(false));
        $this->groupRepo->expects($this->never())
            ->method('getChildren');
        $this->user->expects($this->once())
            ->method('getGroup')
            ->will($this->returnValue(null));

        $this->assertEmpty($this->resolver->getAccessibleGroups());
    }
}

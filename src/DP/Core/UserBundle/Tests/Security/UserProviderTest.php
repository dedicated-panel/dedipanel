<?php

namespace DP\Core\UserBundle\Tests\Security;

use DP\Core\UserBundle\Security\UserProvider;

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userManager;

    /**
     * @var UserProvider
     */
    private $userProvider;

    protected function setUp()
    {
        $this->userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $this->userProvider = new UserProvider($this->userManager);
    }

    public function testRefreshUserBy()
    {
        $user = $this->getMockBuilder('DP\Core\UserBundle\Entity\User')
            ->setMethods(array('getId'))
            ->getMock();

        $user->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('123'));

        $refreshedUser = $this->getMock('DP\Core\UserBundle\Entity\User');
        $this->userManager->expects($this->once())
            ->method('findUserBy')
            ->with(array('id' => '123'))
            ->will($this->returnValue($refreshedUser));

        $this->assertSame($refreshedUser, $this->userProvider->refreshUser($user));
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testRefreshDeleted()
    {
        $user = $this->getMockForAbstractClass('DP\Core\UserBundle\Entity\User');
        $this->userManager->expects($this->once())
            ->method('findUserBy')
            ->will($this->returnValue(null));

        $this->userProvider->refreshUser($user);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshInvalidUser()
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');

        $this->userProvider->refreshUser($user);
    }
}

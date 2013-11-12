<?php

namespace DP\Core\UserBundle\Entity\Factory;

use DP\Admin\AdminBundle\Entity\Factory\FactoryInterface;
use FOS\UserBundle\Model\UserManagerInterface;

class UserFactory implements FactoryInterface
{
    private $manager;
    
    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;
    }
    
    public function createEntity()
    {
        return $this->manager->createUser();
    }
}

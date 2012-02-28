<?php

/*
** Copyright (C) 2010-2012 Kerouanton Albin, Smedts Jérôme
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along
** with this program; if not, write to the Free Software Foundation, Inc.,
** 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace DP\Core\DistributionBundle\Configurator\Step;

use Sensio\Bundle\DistributionBundle\Configurator\Step\Step;
use DP\Core\DistributionBundle\Configurator\Form\UserStepType;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Sensio\Bundle\DistributionBundle\Configurator\Step\StepInterface;

class UserStep extends Step
{
    /**
     * @Assert\NotBlank
     */
    public $username;
    
    /**
     * @Assert\NotBlank
     */
    public $email;
    
    /**
     * @Assert\NotBlank
     */
    public $password;
    
    /**
     * @see StepInterface
     */
    public function getFormType()
    {
        return new UserStepType();
    }
    
    /**
     * @see StepInterface
     */
    public function getTitle()
    {
        return 'Super Admin';
    }
    
    /**
     * @see StepInterface 
     */
    public function getDescription()
    {
        return 'Create an user for administer the panel.';
    }
    
    /**
     * @see StepInterface
     */
    public function update(StepInterface $data)
    {
        $user = $this->userManager->createUser();
        $user->setUsername($data->username);
        $user->setEmail($data->email);
        $user->setPlainPassword($data->password);
        $user->setSuperAdmin(true);
        $user->setEnabled(true);
        $this->userManager->updateUser($user);
        
        return array();
    }
    
    /**
     * @see StepInterface
     */
    public function __construct(array $options)
    {
        $this->userManager = $options['usrMgr'];
    }
}

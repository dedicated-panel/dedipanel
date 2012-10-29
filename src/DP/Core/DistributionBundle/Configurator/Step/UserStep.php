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

use DP\Core\DistributionBundle\Configurator\Form\UserStepType;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Sensio\Bundle\DistributionBundle\Configurator\Step\StepInterface;

class UserStep implements StepInterface
{
    /**
     * @Assert\NotBlank(message="super_admin.username.blank")
     * @Assert\MinLength(limit="2", message="super_admin.username.short")
     * @Assert\MaxLength(limit="255", message="super_admin.username.long")
     */
    public $username;
    
    /**
     * @Assert\NotBlank(message="super_admin.email.blank")
     * @Assert\MinLength(limit="2", message="super_admin.email.short")
     * @Assert\MaxLength(limit="255", message="super_admin.email.long")
     * @Assert\Email(checkMX="true", message="super_admin.email.valid")
     */
    public $email;
    
    /**
     * @Assert\NotBlank(message="super_admin.password.blank")
     * @Assert\MinLength(limit="6", message="super_admin.password.short")
     */
    public $password;
    
    /**
     * @see StepInterface
     */
    public function __construct(array $options)
    {
        $this->userManager = $options['usrMgr'];
    }
    
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
    public function getTemplate()
    {
        return 'DPDistributionBundle:Configurator/Step:user.html.twig';
    }
    
    /**
     * @see StepInterface
     */
    public function checkRequirements()
    {
        return array();
    }
    
    /**
     * @see StepInterface
     */
    public function checkOptionalSettings()
    {
        return array();
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
}

<?php

/*
** Copyright (C) 2010-2013 Kerouanton Albin, Smedts Jérôme
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


use DP\Core\DistributionBundle\Configurator\Step\StepInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use DP\Core\DistributionBundle\Configurator\Form\UserStepType;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserStep implements StepInterface
{
    /**
     * @Assert\NotBlank(message="configurator.userCreation.username.blank")
     * @Assert\MinLength(limit="2", message="configurator.userCreation.username.short")
     * @Assert\MaxLength(limit="255", message="configurator.userCreation.username.long")
     */
    public $username;
    
    /**
     * @Assert\NotBlank(message="configurator.userCreation.email.blank")
     * @Assert\MinLength(limit="8", message="configurator.userCreation.email.short")
     * @Assert\MaxLength(limit="255", message="configurator.userCreation.email.long")
     */
    public $email;
    
    /**
     * 
     * @Assert\NotBlank(message="configurator.userCreation.password.blank")
     * @Assert\MinLength(limit="6", message="configurator.userCreation.password.short")
     */
    public $password;
    
    private $container;
    
    /**
     * @see StepInterface
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
    public function getTitle()
    {
        return 'configurator.userCreation.title';
    }
    
    /**
     * @see StepInterface
     */
    public function run(StepInterface $data, $configType)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        
        $user = $userManager->createUser();
        $user->setUsername($data->username);
        $user->setEmail($data->email);
        $user->setPlainPassword($data->password);
        $user->setSuperAdmin(true);
        $user->setEnabled(true);
        $userManager->updateUser($user);
        
        return array();
    }
    
    public function isInstallStep()
    {
        return true;
    }
    
    public function isUpdateStep()
    {
        return false;
    }
    
    public function checkRequirements()
    {
        return array();
    }
}

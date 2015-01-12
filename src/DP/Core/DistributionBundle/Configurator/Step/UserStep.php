<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\DistributionBundle\Configurator\Step;


use DP\Core\DistributionBundle\Configurator\Step\StepInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use DP\Core\DistributionBundle\Configurator\Form\UserStepType;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Model\UserInterface;

class UserStep implements StepInterface
{
    /**
     * @Assert\NotBlank(message="configurator.user_creation.username.blank")
     * @Assert\Length(
     *      min = 2,   minMessage = "configurator.user_creation.username.short",
     *      max = 255, maxMessage = "configurator.user_creation.username.long"
     * )
     */
    public $username;

    /**
     * @Assert\NotBlank(message="configurator.user_creation.email.blank")
     * @Assert\Email(message="configuration.user_creation.email.valid")
     */
    public $email;

    /**
     * @Assert\NotBlank(message="configurator.user_creation.password.blank")
     * @Assert\Length(min = 6, minMessage = "configurator.user_creation.password.short")
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
        return 'configurator.user_creation.title';
    }

    /**
     * @see StepInterface
     */
    public function run(StepInterface $data, $configType)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();

        if ($user instanceof UserInterface) {

            $user->setUsername($data->username);
            $user->setEmail($data->email);
            $user->setPlainPassword($data->password);
            $user->setSuperAdmin(true);
            $user->setEnabled(true);
        }
     
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

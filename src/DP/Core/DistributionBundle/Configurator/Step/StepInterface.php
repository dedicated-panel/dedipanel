<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\DistributionBundle\Configurator\Step;

use Symfony\Component\Form\Type\FormTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * StepInterface.
 *
 * @author Marc Weistroff <marc.weistroff@sensio.com>
 */
interface StepInterface
{    
    /**
     * __construct
     *
     * @param array $parameters
     */
    function __construct(ContainerInterface $container);

    /**
     * Returns the form used for configuration.
     *
     * @return FormTypeInterface
     */
    function getFormType();

    /**
     * Returns the template to be renderer for this step.
     *
     * @return string
     */
    function getTemplate();
    
    /**
     * Return the title to be used in the breadcrumb
     * 
     * @return string 
     */
    function getTitle();

    /**
     * Updates form data parameters.
     *
     * @param array  $parameters
     * @param string Configuration type (install|update)
     * @return array Errors (or empty array)
     */
    function run(StepInterface $data, $configType);
    
    /**
     * @return bool
     */
    function isInstallStep();
    
    /**
     * @return bool
     */
    function isUpdateStep();
    
    /**
     * @return array 
     */
    function checkRequirements();
}

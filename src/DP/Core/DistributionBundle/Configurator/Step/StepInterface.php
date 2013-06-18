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

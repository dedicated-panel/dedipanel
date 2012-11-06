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

use Sensio\Bundle\DistributionBundle\Configurator\Step\StepInterface;
use DP\Core\DistributionBundle\Configurator\Form\FixturesStepType;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use DP\Core\DistributionBundle\ConsoleOutput\StringOutput;

class FixturesStep implements StepInterface
{
    private $container;
    
    public $configurationType;
    public $loadFixtures = true;
    
    public function __construct(array $parameters)
    {
        $this->container = $parameters['container'];
    }
    
    /**
     * @see StepInterface
     */
    public function getFormType()
    {
        return new FixturesStepType();
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
    public function getTemplate()
    {
        return 'DPDistributionBundle:Configurator/Step:fixtures.html.twig';
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
        if ($data->configurationType == 'install') {
            var_dump($this->executeConsoleCmd('doctrine:schema:create'));
        }
        else {
            $this->executeConsoleCmd('doctrine:schema:update --force');
        }
        
        if ($data->loadFixtures == true) {
            $path = str_replace('\\', '/', __DIR__) . '/../../Fixtures';
            var_dump($this->executeConsoleCmd('doctrine:fixtures:load --fixtures=' . $path));
        }
        
        return array();
    }
    
    private function executeConsoleCmd($cmd)
    {
        chdir($this->container->getParameter('kernel.root_dir') . '/..');
        
        $input = new StringInput($cmd);
        $input->setInteractive(false);
        $output = new StringOutput();
        
        $kernel = $this->container->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);
        
        $application->run($input, $output);
        return $output->getBuffer();
    }
}

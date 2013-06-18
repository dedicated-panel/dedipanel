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

namespace DP\Core\DistributionBundle\Configurator;

use DP\Core\DistributionBundle\Configurator\Step\StepInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Configurator.
 *
 * @author Marc Weistroff <marc.weistroff@gmail.com>
 */
class Configurator
{
    protected $filename;
    protected $installSteps;
    protected $updateSteps;
    protected $parameters;

    public function __construct($kernelDir)
    {
        $this->kernelDir = $kernelDir;
        $this->filename = $kernelDir.'/config/parameters.yml';

        $this->installSteps = array();
        $this->updateSteps = array();
        $this->parameters = $this->read();
    }

    /**
     * @param StepInterface $step
     */
    public function addStep(StepInterface $step)
    {
        if ($step->isInstallStep()) {
            $this->installSteps[] = $step;
        }
        if ($step->isUpdateStep()) {
            $this->updateSteps[] = $step;
        }
    }

    /**
     * @param integer $index
     *
     * @return StepInterface
     */
    public function getInstallStep($index)
    {
        if (isset($this->installSteps[$index])) {
            return $this->installSteps[$index];
        }
    }
    
    /**
     * @param integer $index
     *
     * @return StepInterface
     */
    public function getUpdateStep($index)
    {
        if (isset($this->updateSteps[$index])) {
            return $this->updateSteps[$index];
        }
    }

    /**
     * @return integer
     */
    public function getInstallStepCount()
    {
        return count($this->installSteps);
    }
    
    /**
     * @return integer
     */
    public function getUpdateStepCount()
    {
        return count($this->updateSteps);
    }
    
    /**
     * @return array
     */
    public function getRequirements()
    {
        $requirements = array();
        $error = false;
        
        foreach ($this->installSteps as $step) {
            foreach ($step->checkRequirements() as $label => $state) {
                $requirements[$label] = $state;
                
                if ($state == false) {
                    $error = true;
                }
            }
        }
        
        // Vérification de la présence de l'extension php socket
        $requirements['configurator.socketExtension'] = true;
        if (!function_exists('socket_create')) {
            $requirements['configurator.socketExtension'] = false;
            $error = true;
        }
        
        // Vérification de la présence de l'extension php intl
        $requirements['configurator.intlExtension'] = true;
        if (!defined('INTL_MAX_LOCALE_LEN')) {
            $requirements['configurator.intlExtension'] = false;
            $error = true;
        }

        return array('requirements' => $requirements, 'error' => $error);
    }

    /**
     * Reads parameters from file.
     *
     * @return array
     */
    protected function read()
    {
        $filename = $this->filename;
        if (!$this->isFileWritable() && file_exists($this->getCacheFilename())) {
            $filename = $this->getCacheFilename();
        }

        $ret = Yaml::parse($filename);
        if (false === $ret || array() === $ret) {
            throw new \InvalidArgumentException(sprintf('The %s file is not valid.', $filename));
        }

        if (isset($ret['parameters']) && is_array($ret['parameters'])) {
            return $ret['parameters'];
        } else {
            return array();
        }
    }

    /**
     * Writes parameters to parameters.yml or temporary in the cache directory.
     *
     * @return boolean
     */
    public function write()
    {
        $filename = $this->isFileWritable() ? $this->filename : $this->getCacheFilename();

        return file_put_contents($filename, $this->render());
    }

    /**
     * Renders parameters as a string.
     *
     * @return string
     */
    public function render()
    {
        return Yaml::dump(array('parameters' => $this->parameters));
    }
    
    public function getConfigParameters()
    {
        return $this->read();
    }

    public function isFileWritable()
    {
        return is_writable($this->filename);
    }

    public function clean()
    {
        if (file_exists($this->getCacheFilename())) {
            @unlink($this->getCacheFilename());
        }
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function mergeParameters($parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * getCacheFilename
     *
     * @return string
     */
    protected function getCacheFilename()
    {
        return $this->kernelDir.'/cache/parameters.yml';
    }
}

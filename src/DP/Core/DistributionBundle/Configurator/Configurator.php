<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    protected $kernelDir;

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
     * @param integer $step
     *
     * @return StepInterface
     */
    public function getInstallStep($step)
    {
        if (isset($this->installSteps[$step])) {
            return $this->installSteps[$step];
        }
    }
    
    /**
     * @param integer $step
     *
     * @return StepInterface
     */
    public function getUpdateStep($step)
    {
        if (isset($this->updateSteps[$step])) {
            return $this->updateSteps[$step];
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
        $requirements = [];
        
        foreach ($this->installSteps as $step) {
            $requirements = array_merge($requirements, $this->getStepRequirements($step));
        }

        $requirements = array_merge($requirements, $this->getCoreRequirements());

        return array('requirements' => $requirements, 'error' => in_array(false, $requirements));
    }

    private function getCoreRequirements()
    {
        $requirements = [];

        // Vérification de la présence de l'extension php socket
        $requirements['configurator.socket_extension'] = true;
        if (!function_exists('socket_create')) {
            $requirements['configurator.socket_extension'] = false;
        }

        // Vérification de la présence de l'extension php intl
        $requirements['configurator.intl_extension'] = true;
        if (!defined('INTL_MAX_LOCALE_LEN')) {
            $requirements['configurator.intl_extension'] = false;
        }

        return $requirements;
    }

    private function getStepRequirements(StepInterface $step)
    {
        $requirements = [];

        foreach ($step->checkRequirements() as $label => $state) {
            $requirements[$label] = $state;
        }

        return $requirements;
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
     * @return integer
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
    
    public function getWhitelistFilepath()
    {
        return WHITELIST_FILEPATH;
    }
    
    public function getKernelDir()
    {
        return $this->kernelDir;
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

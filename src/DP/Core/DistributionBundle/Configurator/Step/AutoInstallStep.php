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
use DP\Core\DistributionBundle\Configurator\Form\AutoInstallStepType;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class AutoInstallStep implements StepInterface
{ 
    public $configurationType;
    public $loadFixtures = true;
    
    private $secret;
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $installer = $this->container->get('dp.webinstaller');
        $parameters = $installer->getConfigParameters();
        
        // Chargement du secret actuellement utilisé
        if (array_key_exists('secret', $parameters)) {
            $this->secret = $parameters['secret'];
        }
    }
    
    /**
     * @see StepInterface
     */
    public function getTitle()
    {
        return 'auto_install.title';
    }
    
    /**
     * @see StepInterface
     */
    public function getFormType()
    {
        return new AutoInstallStepType();
    }

    /**
     * @see StepInterface
     */
    public function getTemplate()
    {
        return 'DPDistributionBundle:Configurator/Step:autoInstall.html.twig';
    }
    
    /**
     * @see StepInterface
     */
    public function run(StepInterface $data, $configType)
    {
        $return = array();
        
        // Installation de la base de données
        $return = array_merge($return, $this->databaseInstallation($configType, $data->loadFixtures));

        // Installation automatique des assets
        $return = array_merge($return, $this->installAssets());
        
        if (!isset($this->secret) || 
            'ThisTokenIsNotSoSecretChangeIt' == $this->secret) {
            $this->secret = $this->generateRandomSecret();
            
            $configurator = $this->container->get('dp.webinstaller');
            
            if ($configurator->isFileWritable()) {
                $configurator->mergeParameters(array('secret' => $this->secret));
                
                if (!$configurator->write()) {
                    $return[] = 'An error occured while writing the app/config/parameters.yml file.';
                }
            }
            else {
                $return[] = 'Your app/config/parameters.yml is not writable.';
            }
            
        }
        
        return $return;
    }
    
    protected function databaseInstallation($configurationType, $loadFixtures)
    {
        $errors = array();
        
        // Création du schemaTool et récupération des metadatas relatives aux entités
        $em = $this->container->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($em);
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        
        if ($configurationType == 'install') {
            // Création de la bdd
            try {
                $schemaTool->createSchema($metadatas);
            }
            catch (ToolsException $e) {
                $errors[] = $e->getMessage();
            }
        }
        else {
            // Mise à jour de la bdd
            try {
                $schemaTool->updateSchema($metadatas, true);
            }
            catch (ToolsException $e) {
                $errors[] = $e->getMessage();
            }
        }
        
        // Chargement des données de config du panel et des jeux fourni
        if ($loadFixtures == true) {
            $errors = array_merge($errors, $this->loadFixtures(__DIR__ . '/../../Fixtures'));
        }
            
        return $errors;
    }
    
    protected function loadFixtures($path)
    {
        $doctrine = $this->container->get('doctrine');
        $em = $doctrine->getManager();

        $loader = new DataFixturesLoader($this->container);
        $loader->loadFromDirectory($path);
        $fixtures = $loader->getFixtures();
        
        if (!$fixtures) {
            return array(sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $paths)));
        }
        
        $errors = array();
        
        try {
            $purger = new ORMPurger($em);
            $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
            
            $executor = new ORMExecutor($em, $purger);
            $executor->execute($fixtures);
        }
        catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }
        
        
        return $errors;
    }
    
    protected function installAssets($targetArg = '.')
    {
        $filesystem = $this->container->get('filesystem');
        
        // Create the bundles directory otherwise symlink will fail.
        $filesystem->mkdir($targetArg.'/bundles/', 0777);
        
        foreach ($this->container->get('kernel')->getBundles() as $bundle) {
            if (is_dir($originDir = $bundle->getPath().'/Resources/public')) {
                $bundlesDir = $targetArg.'/bundles/';
                $targetDir  = $bundlesDir.preg_replace('/bundle$/', '', strtolower($bundle->getName()));

                $filesystem->remove($targetDir);
                $filesystem->symlink($originDir, $targetDir);
            }
        }
        
        return array();
    }    

    protected function generateRandomSecret()
    {
        return hash('sha1', uniqid(mt_rand()));
    }
    
    public function isInstallStep()
    {
        return true;
    }
    
    public function isUpdateStep()
    {
        return true;
    }
    
    public function checkRequirements()
    {
        return array();
    }
}

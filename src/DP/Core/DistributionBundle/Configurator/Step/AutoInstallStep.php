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
use DP\Core\DistributionBundle\Configurator\Form\AutoInstallStepType;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class AutoInstallStep implements StepInterface
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
        return new AutoInstallStepType();
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
        return 'DPDistributionBundle:Configurator/Step:autoInstall.html.twig';
    }
    
    /**
     * @see StepInterface
     */
    public function checkOptionalSettings()
    {
        if (!function_exists('symlink')) {
            return array('The symlink() function is not available on your system. You need to install the assets without the --symlink option.');
        }
        
        return array();
    }
    
    /**
     * @see StepInterface
     */
    public function update(StepInterface $data)
    {
        // Installation de la base de données
        $this->databaseInstallation($data->configurationType, $data->loadFixtures);
        
        // Installation automatique des assets
        $this->installAssets();
        
        return array();
    }
    
    protected function databaseInstallation($configurationType, $loadFixtures)
    {
        // Création du schemaTool et récupération des metadatas relatives aux entités
        $em = $this->container->get('doctrine')->getEntityManager();
        $schemaTool = new SchemaTool($em);
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        
        if ($configurationType == 'install') {
            // Création de la bdd
            $schemaTool->createSchema($metadatas);
        }
        else {
            // Mise à jour de la bdd
            $schemaTool->updateSchema($metadatas, true);
        }
        
        // Chargement des données de config du panel et des jeux fourni
        if ($loadFixtures == true) {
            $this->loadFixtures(__DIR__ . '/../../Fixtures');
        }
    }
    
    protected function loadFixtures($path)
    {
        $doctrine = $this->container->get('doctrine');
        $em = $doctrine->getManager();

        $loader = new DataFixturesLoader($this->container);
        $loader->loadFromDirectory($path);
        $fixtures = $loader->getFixtures();
        if (!$fixtures) {
            throw new InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $paths))
            );
        }
        
        $purger = new ORMPurger($em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($fixtures);
    }
    
    protected function installAssets($targetArg = 'web')
    {
        $filesystem = $this->getContainer()->get('filesystem');
        
        // Create the bundles directory otherwise symlink will fail.
        $filesystem->mkdir($targetArg.'/bundles/', 0777);
        
        foreach ($this->getContainer()->get('kernel')->getBundles() as $bundle) {
            if (is_dir($originDir = $bundle->getPath().'/Resources/public')) {
                $bundlesDir = $targetArg.'/bundles/';
                $targetDir  = $bundlesDir.preg_replace('/bundle$/', '', strtolower($bundle->getName()));

                $filesystem->remove($targetDir);
                $filesystem->symlink($originDir, $targetDir);
            }
        }
    }
}

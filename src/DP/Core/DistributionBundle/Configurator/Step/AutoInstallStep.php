<?php

/**
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    public $createDB      = true;
    public $loadFixtures  = true;
    public $installAssets = true;
    private $secret;
    private $container;
    private $installer;
    private $manager;
    private $schemaTool;
    private $metadatas;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->installer = $this->container->get('dp.webinstaller');

        // Chargement du secret actuellement utilisé
        $parameters = $this->installer->getConfigParameters();
        if (array_key_exists('secret', $parameters)) {
            $this->secret = $parameters['secret'];
        }

        // Création du schemaTool et récupération des metadatas relatives aux entitées
        $this->manager     = $this->container->get('doctrine')->getManager();
        $this->schemaTool = new SchemaTool($this->manager);
        $this->metadatas   = $this->manager->getMetadataFactory()->getAllMetadata();
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
        $errors = [];
        
        // Installation/Maj des tables
        if ($this->createDB == true) {
            $errors = $this->createDatabase();

            if (!empty($errors)) return $errors;
        }

        if ($this->isDBEmpty()) {
            return array('configurator.db.empty');
        }

        // Chargement des fixtures
        if ($this->loadFixtures == true) {
            $errors = array_merge($errors, $this->loadFixtures(__DIR__ . '/../../Fixtures'));

            if (!empty($errors)) return $errors;
        }

        // Installation automatique des assets
        if ($this->installAssets == true) {
            $errors = $this->installAssets();
        }

        $errors = array_merge($errors, $this->updateSecretToken());

        return $errors;
    }
    
    private function createDatabase()
    {
        $errors = array();

        if (!$this->isDBEmpty()) {
            return array('configurator.db.not_empty');
        }

        // Création de la bdd
        try {
            $this->schemaTool->createSchema($this->metadatas);
        }
        catch (ToolsException $e) {
            $errors[] = $e->getMessage();
        }

        return $errors;
    }

    private function updateDatabase()
    {
        $errors = array();

        // Mise à jour de la bdd
        try {
            $this->schemaTool->updateSchema($this->metadatas, true);
        }
        catch (ToolsException $e) {
            $errors[] = $e->getMessage();
        }

        return $errors;
    }
    
    private function loadFixtures($path)
    {
        $doctrine = $this->container->get('doctrine');
        $em = $doctrine->getManager();

        $loader = new DataFixturesLoader($this->container);
        $loader->loadFromDirectory($path);
        $fixtures = $loader->getFixtures();

        if (!$fixtures) {
            return array(sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $path)));
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
    
    private function installAssets($targetArg = '.')
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

    private function updateSecretToken()
    {
        $errors = [];

        if (!isset($this->secret) ||
            'ThisTokenIsNotSoSecretChangeIt' == $this->secret) {
            $this->secret = $this->generateRandomSecret();

            if ($this->installer->isFileWritable()) {
                $this->installer->mergeParameters(array('secret' => $this->secret));

                if (!$this->installer->write()) {
                    $errors[] = 'An error occured while writing the app/config/parameters.yml file.';
                }
            }
            else {
                $errors[] = 'Your app/config/parameters.yml is not writable.';
            }
        }

        return $errors;
    }

    private function isDBEmpty()
    {
        return count($this->manager->getConnection()->executeQuery('SHOW TABLES')->fetchAll()) == 0;
    }

    private function generateRandomSecret()
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

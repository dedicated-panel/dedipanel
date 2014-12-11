<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\DistributionBundle\Configurator\Step;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;
use Doctrine\DBAL\Migrations\MigrationException;
use Doctrine\DBAL\Migrations\OutputWriter;
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
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
    private $container;
    /** @var DP\Core\DistributionBundle\Configurator\Configurator $installer */
    private $installer;
    /** @var string $secret */
    private $secret;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->installer = $this->container->get('dp.webinstaller');

        // Chargement du secret actuellement utilisé
        $parameters = $this->installer->getConfigParameters();
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
        if ('install' === $configType && !$this->isDbEmpty()) {
            return ['configurator.db.not_empty'];
        }

        $errors = $this->runMigrations();

        if (!isset($this->secret) || 'ThisTokenIsNotSoSecretChangeIt' == $this->secret) {
            $errors += $this->updateSecretToken();
        }

        return $errors;
    }

    private function runMigrations()
    {
        $errors    = [];
        $migration = new Migration($this->getMigrationConfig($errors));

        try {
            $migration->migrate();
        }
        catch (MigrationException $e) { }

        return $errors;
    }

    /**
     * @return Configuration
     */
    private function getMigrationConfig(array &$errors)
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        $config = new Configuration($connection, new OutputWriter(function ($message) use ($errors) {
            $matches = [];

            if (preg_match('#<error>(.*)</error>#', $message, $matches)) {
                $errors += $matches;
            }
        }));

        $config->setName($this->container->getParameter('doctrine_migrations.name'));
        $config->setMigrationsTableName($this->container->getParameter('doctrine_migrations.table_name'));
        $config->setMigrationsNamespace($this->container->getParameter('doctrine_migrations.namespace'));
        $config->setMigrationsDirectory($this->container->getParameter('doctrine_migrations.dir_name'));

        // Register Migrations as there are not registered by Configuration
        $config->registerMigrationsFromDirectory($config->getMigrationsDirectory());

        return $config;
    }

    /**
     * @return array
     */
    private function updateSecretToken()
    {
        $errors = [];

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

        return $errors;
    }

    private function generateRandomSecret()
    {
        return hash('sha1', uniqid(mt_rand()));
    }

    private function isDbEmpty()
    {
        return count($this->container->get('doctrine.dbal.default_connection')->executeQuery('SHOW TABLES')->fetchAll()) == 0;
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

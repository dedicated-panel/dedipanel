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

use Symfony\Component\DependencyInjection\ContainerInterface;
use DP\Core\DistributionBundle\Configurator\Form\DoctrineStepType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Doctrine Step.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DoctrineStep implements StepInterface
{
    /**
     * @Assert\NotBlank(message="configurator.db.host.blank")
     */
    public $host;
    
    public $port;

    /**
     * @Assert\NotBlank(message="configurator.db.name.blank")
     */
    public $name;

    /**
     * @Assert\NotBlank(message="configurator.db.user.blank")
     */
    public $user;

    public $password;
    
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $installer = $container->get('dp.webinstaller');
        $parameters = $installer->getConfigParameters();
        
        foreach ($parameters as $key => $value) {
            if (0 === strpos($key, 'database_')) {
                $parameters[substr($key, 9)] = $value;
                $key = substr($key, 9);
                $this->$key = $value;
            }
        }
    }

    /**
     * @see StepInterface
     */
    public function getFormType()
    {
        return new DoctrineStepType();
    }

    /**
     * @see StepInterface
     */
    public function checkRequirements()
    {
        $messages = array();

        $messages['configurator.pdoMandatory'] = true;
        $messages['configurator.mysqlExtensionMandatory'] = true;
        $messages['configurator.sqliteExtensionMandatory'] = true;
        
        if (!class_exists('\PDO')) {
             $messages['configurator.pdoMandatory'] = false;
             $messages['configurator.mysqlExtensionMandatory'] = false;
             $messages['configurator.sqliteExtensionMandatory'] = false;
        } else {
            $drivers = \PDO::getAvailableDrivers();
            
            if (!in_array('mysql', $drivers)) {
                $messages['configurator.mysqlExtensionMandatory'] = false;
            }
            if (!in_array('sqlite', $drivers)) {
                $messages['configurator.sqliteExtensionMandatory'] = false;
            }
        }

        return $messages;
    }

    /**
     * @see StepInterface
     */
    public function checkOptionalSettings()
    {
        return array();
    }
    
    public function run(StepInterface $data, $configType)
    {
        $configurator = $this->container->get('dp.webinstaller');
        $parameters = array();

        unset($data->container);
        
        foreach ($data as $key => $value) {
            $parameters['database_'.$key] = $value;
        }

        // Modifie également le secret
//        $parameters['secret'] = hash('sha1', uniqid(mt_rand()));
        
        $configurator->mergeParameters($parameters);
        $configurator->write();
        
        return $configurator->isFileWritable();
    }

    /**
     * @see StepInterface
     */
    public function getTemplate()
    {
        return 'DPDistributionBundle:Configurator/Step:doctrine.html.twig';
    }
    
    public function getTitle()
    {
        return 'configurator.db.title';
    }

    /**
     * @return array
     */
    static public function getDriverKeys()
    {
        return array_keys(static::getDrivers());
    }

    /**
     * @return array
     */
    static public function getDrivers()
    {
        return array(
            'pdo_mysql'  => 'MySQL (PDO)',
            'pdo_sqlite' => 'SQLite (PDO)',
            'pdo_pgsql'  => 'PosgreSQL (PDO)',
            'oci8'       => 'Oracle (native)',
            'ibm_db2'    => 'IBM DB2 (native)',
            'pdo_oci'    => 'Oracle (PDO)',
            'pdo_ibm'    => 'IBM DB2 (PDO)',
            'pdo_sqlsrv' => 'SQLServer (PDO)',
        );
    }
    
    public function isInstallStep()
    {
        return true;
    }
    
    public function isUpdateStep()
    {
        return false;
    }
}

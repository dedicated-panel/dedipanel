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
use Doctrine\DBAL\DriverManager;

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
                
                switch ($key) {
                    case 'host':
                    case 'port':
                    case 'name':
                    case 'user':
                    case 'password':
                        $this->$key = $value;
                    break;
                }
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
        $errors = array();
        $configurator = $this->container->get('dp.webinstaller');
        $parameters = array();
        
        foreach ($data as $key => $value) {
            $parameters['database_' . $key] = $value;
        }
        
        // var_dump($data);
        
        // Vérifie les paramètres de bdd passés
        $goodParams = false;
        try {
            $conn = DriverManager::getConnection(array(
                'driver' => 'pdo_mysql',
                'user' => $data->user, 
                'password' => $data->password, 
                'host' => $data->host, 
                'port' => $data->port, 
                'dbname' => $data->name, 
            ));
        
            $conn->connect();
            $conn->close();
            
            $goodParams = true;
        }
        catch (\Exception $e) {}
        
        if ($goodParams) {
            if ($configurator->isFileWritable()) {
                $configurator->mergeParameters($parameters);
                
                if (!$configurator->write()) {
                    $errors[] = 'An error occured while writing the app/config/parameters.yml file.';
                }
                
                // Suppression "hard" du cache (sinon les nouveaux paramètres ne sont pas pris en compte)
                $cacheFile = $configurator->getKernelDir() . '/cache/installer/appInstallerProjectContainer.php';
                if (file_exists($cacheFile)) {
                    unlink($cacheFile);
                }
            }
            else {
                $errors[] = 'Your app/config/parameters.yml is not writable.';
            }
        }
        else {
            $errors[] = 'configurator.db.connectionTest';
        }
        
        return $errors;
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

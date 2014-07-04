<?php

/**
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
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

        $messages['configurator.pdo_mandatory'] = true;
        $messages['configurator.mysql_extension_mandatory'] = true;
        
        if (!class_exists('\PDO')) {
             $messages['configurator.pdo_mandatory'] = false;
             $messages['configurator.mysql_extension_mandatory'] = false;
        } else {
            $drivers = \PDO::getAvailableDrivers();
            
            if (!in_array('mysql', $drivers)) {
                $messages['configurator.mysql_extension_mandatory'] = false;
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
        if (!$data instanceof DoctrineStep) {
            throw new \RuntimeException("Can't manage another step that itself.");
        }

        $errors = array();
        $configurator = $this->container->get('dp.webinstaller');
        $parameters = array();

        foreach ($data as $key => $value) {
            $parameters['database_' . $key] = $value;
        }

        if (!$configurator->isFileWritable()) {
            $errors[] =  'Your app/config/parameters.yml is not writable.';
        }

        // Need to add the same suffix as from app/config/config_test.yml to the database name
        if (!$this->testConnection($data->host, $data->user, $data->password, $data->port, $data->name . '_test')) {
            return array_merge($errors, array('configurator.db.connectionTest'));
        }

        $configurator->mergeParameters($parameters);

        if (!$configurator->write()) {
            return array_merge($errors, array('An error occured while writing the app/config/parameters.yml file.'));
        }

        // Suppression "hard" du cache (sinon les nouveaux paramètres ne sont pas pris en compte)
        $cacheFile = $configurator->getKernelDir() . '/cache/installer/appInstallerProjectContainer.php';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Trying to connect to the database
     *
     * @return bool
     */
    private function testConnection($host, $user, $password, $port, $dbname)
    {
        try {
            $conn = DriverManager::getConnection(array(
                'driver' => 'pdo_mysql',
                'user' => $user,
                'password' => $password,
                'host' => $host,
                'port' => $port,
                'dbname' => $dbname,
            ));

            $conn->connect();
            $conn->close();

            return true;
        }
        catch (\Exception $e) {
            var_dump($e->getMessage());
        }

        return false;
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

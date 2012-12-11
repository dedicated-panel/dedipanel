<?php
namespace DP\Core\MachineBundle\PHPSeclibWrapper;

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
require_once __DIR__ . '/Crypt/RSA.php';
require_once __DIR__ . '/Net/SSH2.php';
require_once __DIR__ . '/Net/SFTP.php';
use PHPSeclib;

require_once __DIR__ . '/Exception.php';
use PHPSeclibWrapper\Exception;

use DP\Core\MachineBundle\Entity;

//define('NET_SSH2_LOGGING', NET_SSH2_LOG_SIMPLE);
//define('NET_SFTP_LOGGING', NET_SFTP_LOG_SIMPLE);
define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX);
define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);

set_time_limit(0);

// Change namespace of wrapper

/**
 * TODO: Mise en cache keyfile ?!
 * TODO: Verif HostKey
 *
 * @author NiR
 */
class PHPSeclibWrapper {   
    private $host;
    private $port;
    private $user;
    private $home;
    private $passwd;
    private $keyfile;
    private $privateKey;
    private $debug;
    
    private $ssh;
    private $sftp;
    
    private static $servers = array();
    
    const UPLOAD_DATA = 1;
    const UPLOAD_FILE = 2;
    
    
    private function __construct($host, $port, $user, $home = null, $keyfile = null, 
        $passwd = null, $debug = false)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->home = $home;
        $this->passwd = $passwd;
        $this->debug = $debug;
        
        // On vérifie s'il y a un keyfile de fourni au constructeur
        // Et si le fichier existe, on charge la clé privée
        if (!is_null($keyfile)) {
            $this->keyfile = $keyfile;
        }
        elseif (!is_null($passwd)) {
            $this->passwd = $passwd;
        }
    }
    public static function getFromMachineEntity(Entity\Machine $machine, $loadKey = true)
    {
        $host = $machine->getPrivateIp();
        $port = $machine->getPort();
        $user = $machine->getUser();
        $home = $machine->getHome();
        $keyfile = null;
        
        if ($loadKey == true) {
            $keyfile = $machine->getPrivateKeyFilename();
        }
        
        return self::get($host, $port, $user, $home, $keyfile);
    }
    /**
     * Get a instance of this class for a server
     *
     * @param string $host          Must be a valid IPv4 address
     * @param int $port
     * @param string $user
     * @param string|null $home     Absolute path of user home on the server
     * @param string|null $keyfile  File containing private key (can be null if $passwd is used)
     * @param string|null $passwd   Password for user authentication (if not using $keyfile)
     * @param int|false $debug  
     * @return PHPSeclibWrapper
     */
    public static function get($host, $port, $user, $home = null, $keyfile = null, 
        $passwd = null, $debug = false)
    {
        $id = $user . '@' . $host . ':' . $port;
        
        if (!array_key_exists($id, self::$servers)) {
            $serv = new self($host, $port, $user, $home, $keyfile, $passwd, $debug);
            self::$servers[$id] = $serv;
        }
        else {
            $serv = self::$servers[$id];
            $serv->setDebug($debug);
        }
        
        return $serv;
    }
    /**
     * Prevents object cloning
     */
    private function __clone() {}
    
    private function getPrivateKeyFilepath($privateKeyFilename = null)
    {
        $path = __DIR__ . '/../Resources/config/.ssh/';
        
        if ($privateKeyFilename === null)
            return $path . $this->keyfile;
        else
            return $path . $privateKeyFilename;
    }
    
    /**
     * Get a phpseclib's ssh instance logged in the server
     * With the private key (if known), or the password (if known too)
     * Or thrown an Exception
     * 
     * Prevents any re-instantiation for a server 
     * And the re-cpomputing of the ssh encryption.
     * 
     * @return PHPSeclib\Net\SSH
     */
    public function getSSH()
    {
        if (!isset($this->ssh)) {
            $ssh = new PHPSeclib\Net\SSH2($this->host, $this->port);
            
            if ($this->privateKey != null || $this->keyfile != null) {
                $ssh->login($this->user, $this->getPrivateKey());
            }
            elseif ($this->passwd != null) {
                $ssh->login($this->user, $this->passwd);
            }
            else {
                throw new Exception\IncompleteLoginID($this);
            }
            
            $this->ssh = $ssh;
        }
        
        return $this->ssh;
    }   
    /**
     * Get a phpseclib's sftp instance
     * 
     * @return \PHPSeclib\Net\SFTP
     */
    public function getSFTP()
    {
        if (!isset($this->sftp)) {
            $sftp = new PHPSeclib\Net\SFTP($this->host, $this->port);
            
            if ($this->privateKey != null || $this->keyfile != null) {
                $sftp->login($this->user, $this->getPrivateKey());
            }
            elseif ($this->passwd != null) {
                $sftp->login($this->user, $this->passwd);
            }
            else {
                throw new Exception\IncompleteLoginID($this);
            }
            
            $this->sftp = $sftp;
        }
        
        return $this->sftp;
    }
    
    /**
     * Execute $cmd on the server and return the value of the command
     * 
     * @param string $cmd
     * @return string
     */
    public function exec($cmd)
    {
        $ret = $this->getSSH()->exec($cmd);
        $ret = trim($ret);
        
        if ($this->debug) {
            // TODO: Add debug via monolog
        }
        
        return $ret;
    }
    
    /**
     * Verify that we can connect to the server
     * 
     * @return bool
     */
    public function connectionTest()
    {
        try {
            $echo = $this->exec('echo a');
        }
        catch (\ErrorException $e) {
            return false;
        }
        
        if (empty($echo) || $echo != 'a') {
            throw new Exception\ConnectionErrorException($this);
        }
        
        return true;
    }
    
    private function getPrivateKey()
    {
        if (!isset($this->privateKey)) {
            $privkeyFilepath = $this->getPrivateKeyFilepath();
            if (!file_exists($privkeyFilepath)) {
                throw new Exception\FileNotFoundException(
                    'has been not found.', $this);
            }
            
            $fileContent = file_get_contents($this->getPrivateKeyFilepath());
            if (empty($fileContent)) {
                throw new Exception\EmptyKeyfileException('is empty.', $this);
            }
            
            $privKey = new PHPSeclib\Crypt\RSA();
            $privKey->loadKey($fileContent);
            $this->privateKey = $privKey;
        }
        
        return $this->privateKey;
    }
    
    public function createKeyPair($privateKeyFilename)
    {
        // Generating key pair
        $rsa = new PHPSeclib\Crypt\RSA();
        $rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_OPENSSH);
        $keyPair = $rsa->createKey();
        
        // Opening the private key file for saving it
        $file = fopen($this->getPrivateKeyFilepath($privateKeyFilename), 'w');
        if (!$file) {
            throw new Exception\FileNotFoundException(
                'cant\'t be created.', $this);
        }
        fwrite($file, $keyPair['privatekey'], strlen($keyPair['privatekey']));
        fclose($file);
        
        // Verifying if .ssh directory in user home exists
        // Create it if do not.
        // And add public key at ~/.ssh/authorized_keys file
        $this->exec ('if [ ! -e ~/.ssh ]; then mkdir ~/.ssh; fi');
        $this->exec('chmod 700 ~/.ssh && 
            echo "' . $keyPair['publickey'] . '" >> ~/.ssh/authorized_keys');
        
        return $keyPair['publickey'];
    }
    public function deleteKeyPair($publicKey)
    {        
        try {
            $publicKey = str_replace('/', '\/', $publicKey);
            $this->exec('cd ~/.ssh/ && sed -i "/^' . $publicKey . '/d" authorized_keys');
            unlink($this->getPrivateKeyFilepath());
            
            return true;
        }
        catch (\ErrorException $e) {
            return false;
        }
    }
    
    /**
     * Set private key filename
     * 
     * @param string $keyfile 
     */
    public function setKeyfile($keyfile)
    {
        $this->keyfile = $keyfile;
    }
    /**
     * Get private key filename
     * 
     * @return string
     */
    public function getKeyfile()
    {
        return $this->keyfile;
    }
    
    /**
     * Upload $data in $remoteFile and change his chmod for $chmod
     * If $type equal UPLOAD_FILE, $data need to be a valid local file
     * The local file is read and upload
     * 
     * @param string      $remoteFile
     * @param string      $data
     * @param octal|false $chmod
     * @param const       $type
     * @return PHPSeclib\Net\SFTP
     */
    public function upload($remoteFile, $data, $chmod = 0750, $type = PHPSeclibWrapper::UPLOAD_DATA)
    {
        if ($type == PHPSeclibWrapper::UPLOAD_FILE) {
            $data = file_get_contents($data);
        }
        
        $sftp = $this->getSFTP();
        $ret = $sftp->put($remoteFile, $data);
        
        if ($chmod !== false) {
            $sftp->chmod($chmod, $remoteFile);
        }
        
        return $ret;
    }
    
    /**
     * Get the file $remoteFile on the server
     * 
     * @param string $remoteFile    Remote filepath
     * @return bool|string          Contains of remote file
     */
    public function getRemoteFile($remoteFile)
    {
        return $this->getSFTP()->get($remoteFile);
    }
    
    
    /**
     * Set host
     * 
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }
    /**
     * Get host
     * 
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
    
    /**
     * Set port
     * 
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }
    /**
     * Get port
     * 
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
    
    /**
     * Set user
     * 
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
    /**
     * Get user
     *
     * @return type 
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set user's home directory
     * 
     * @param type $home 
     */
    public function setHome($home)
    {
        $this->home = $home;
    }
    /**
     * Get home dir of user on the server
     * This getter does not return the $home value
     * 
     * @return type 
     */
    public function getHome()
    {
        $pwd = $this->exec('cd ~ && pwd');
        
        if (empty($pwd)) {
            throw new Exception\ConnectionErrorException($this);
        }
        else {
            $this->home = $pwd;
            return $pwd;
        }
    }
    
    /**
     * Set password
     * 
     * @param string $passwd 
     */
    public function setPasswd($passwd)
    {
        $this->passwd = $passwd;
    }
    /**
     * Get password
     * 
     * @return string
     */
    public function getPasswd()
    {
        return $this->passwd;
    }
    
    /**
     * Set debug
     * 
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }
    /**
     * Get debug
     * 
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }
    
    public function touch($file)
    {
        return $this->getSSH()->exec('touch ' . $file);
    }
}
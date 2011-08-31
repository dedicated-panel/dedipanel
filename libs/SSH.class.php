<?php
// On modifie les répertoires d'include par défaut
// Afin d'inclure facilement la lib phpseclib
set_include_path(get_include_path() . PATH_SEPARATOR . LIBS_DIR . 'phpseclib');

include 'Crypt/RSA.php';
include 'Net/SSH2.php';
include 'Net/SFTP.php';

//define('NET_SSH2_LOGGING', NET_SSH2_LOG_SIMPLE);
//define('NET_SFTP_LOGGING', NET_SFTP_LOG_SIMPLE);
define('HET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX);
define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);

class SSH {
    private function __construct(
        $host, $port, $user, $keyfile, $debug = false, $passwd = null, $ssh = null) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;

        $this->debug = $debug;

        // Si $keyfile vaut "false", c'est qu'on souhaite se connecter avec le mdp (et non la clé)
        if ($keyfile != false) {
            $this->keyfile = $keyfile;
            $content_keyfile = file_get_contents($this->getPrivKeyPath());
            
            if ($content_keyfile == false) {
                throw new Exception('Impossible de charger la clé du serveur.');
                return false;
            }

            $key = new Crypt_RSA();
            $key->loadKey($content_keyfile);
            $this->key = $key;
        }
        else {
            $this->keyfile = false;
            $this->passwd = $passwd;
            $this->ssh = $ssh;
        }
    }

    public static function get($host, $port, $user, $keyfile, $debug = false) {
        $ident = $user . '@' . $host . ':' . $port;

        if (isset(self::$servers[$ident])) {
            $serv = self::$servers[$ident];
            $serv->setDebug($debug);
            return $serv;
        }
        else {
            $serv = new self($host, $port, $user, $keyfile, $debug);
            self::$servers[$ident] = $serv;
            return $serv;
        }
    }

    // Permet de vérifier que les identifiants passé à la méthode sont corrects
    public static function isValidIdents($host, $port, $user, $passwd) {
        $ssh = new Net_SSH2($host, $port);
        $login = $ssh->login($user, $passwd);

        if ($login == false)    return false;
        else return             new self($host, $port, $user, false, false, $passwd, $ssh);
    }
    // Permet de vérifier que la connexion au serveur ssh s'effectue correctement.
    public function testConnexion() {
        $home = '/home/' . $this->user;
        $cmd = $this->exec ('cd ' . $home . ' && pwd');

        return ($cmd == $home) ? true : false;
    }

    // Cette méthode permet de générer une paire de clé et de la sauvegarder
    public function createKeyPair($privkey_filename) {
        $rsa = new Crypt_RSA();
        // On modifie le format de clé publique utilisé afin qu'il soit accepté par OpenSSH
        $rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_OPENSSH);

        // On augmente le temps d'exécution maximum
        // Afin de ne pas avoir de problème avec les libs de maths
        set_time_limit(90);

        // On génère la paire de clé qui sont contenus dans $privatekey et $publickey
        extract($rsa->createKey());

        // On sauvegarde notre clé privée
        $privkeyFile = fopen($this->getPrivKeyPath($privkey_filename), 'w');
        if (!$privkeyFile) throw new Exception ('Impossible de créer la clé privée.');
        fwrite($privkeyFile, $privatekey, strlen($privatekey));
        fclose($privkeyFile);

        // On se connecte au serveur SFTP afin d'uploader la clé publique
        // On commence par vérifier la présence du dossier .ssh et du fichier authorized_keys
        $sftp = new Net_SFTP($this->host, $this->port);
        if (!$sftp->login($this->user, $this->passwd)) {
            throw new Exception ('Impossible de se connecter au serveur SFTP.');
        }

        // On vérifie la présence du dossier ".ssh" de l'utilisateur et on le créer si nécessaire
        if (array_search('.ssh', $sftp->nlist('')) === false) {
            $sftp->mkdir('.ssh');
        }

        // On modifie le chmod du .ssh afin de s'assurer que les clé soit un minimum "secure"
        $sftp->chmod(0700, '.ssh');

        // On liste le contenu du dossier ".ssh" afin de vérifier la présence
        // du fichier "authorized_keys" afin de le créer si nécessaire
        if (array_search('authorized_keys', $sftp->nlist('.ssh')) === false) {
            $this->ssh->exec('touch .ssh/authorized_keys');
        }

        // On récupère le contenu du fichier distant ".ssh/authorized_keys"
        // On y ajoute la clé publique généré
        $autho_keys  = $sftp->get('.ssh/authorized_keys');
        $autho_keys .= $publickey . "\n";

        // On upload le fichier modifié
        $sftp->put('.ssh/authorized_keys', $autho_keys);

        return true;
    }
   
    // Cette méthode permet de supprimer une paire de clé (aussi bien sur le serv qu'en locale)
    public function deleteKeyPair($privkeyFilename) {
        unlink($this->getPrivKeyPath($privkeyFilename));
        // TODO: Suppression bourrine !
        return $this->exec('rm -f ~/.ssh/authorized_keys');
    }
    
    // Cette méthode retourne le chemin de la clé privée
    private function getPrivKeyPath($filename = false) {
        if ($filename == false) $filename = $this->keyfile;
        return CFG_DIR . 'keys/' . $filename;
    }

    public function exec($cmd) {
        if ($ssh = $this->getSSH()) {
            $return = trim($ssh->exec($cmd));

            if ($this->debug) {
//                var_dump($ssh->getLog());
                $this->log[] = $ssh->getLog();
            }

            return $return;
        }
        else return false;
    }
    public function putData($data, $remoteFile) {
        $sftp = $this->getSFTP();
        $sftp->put($remoteFile, $data);
    }
    public function putFile($localFile, $remoteFile) {
        $this->putData(file_get_contents($localFile), $remoteFile);
    }

    public function getDirList($dir) {
        $sftp = $this->getSFTP();
        $rawList = $sftp->rawlist($dir);
        $dirs = array(); $files = array();

        foreach ($rawList AS $file => $attr) {
            if ($attr['type'] == NET_SFTP_TYPE_DIRECTORY) $dirs[] = $file .'/';
            else $files[] = $file;
        }

        return array($dirs, $files);
    }
    public function getFile($file) {
        $sftp = $this->getSFTP();
        return $sftp->get($file);
    }

    /*public function getDirList($dir = '.') {
        $ssh = $this->getSSH();
        $list = $ssh->exec('ls -l ' . $dir);
        var_dump($list);

        return array();

//        return array_merge($dirs, $files);
    }*/

    // Ces deux méthodes permettent d'accéder et d'instancier ssh et sftp
    private function getSSH() {
        if (!isset($this->ssh)) {
            $this->ssh = new Net_SSH2($this->host, $this->port);
            
            if (!$this->ssh->login($this->user, $this->key)) {
                throw new Exception('Impossible de se connecter avec la clé utilisé.');
                return false;
            }
        }

        return $this->ssh;
    }
    private function getSFTP() {
        if (!isset($this->sftp)) {
            $this->sftp = new Net_SFTP($this->host, $this->port);

            if (!$this->sftp->login($this->user, $this->key)) {
                throw new Exception('Impossible de se connecter avec la clé utilisé.');
                return false;
            }
        }

        return $this->sftp;
    }

    public function getDebug() {
        return $this->debug;
    }
    public function setDebug($debug) {
        $this->debug = $debug;
    }
    public function getLog() {
        return $this->log;
    }

    private $host;
    private $port;
    private $user;
    private $passwd;

    private $key;
    private $keyfile;

    private $ssh;
    private $sftp;

    private $debug = false;
    private $log = array();

    private static $servers;
}
?>
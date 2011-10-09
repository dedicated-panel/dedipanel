<?php
include_once LIBS_DIR . 'Core/HTTP.class.php';
include_once LIBS_DIR . 'Core/Page.class.php';
include_once LIBS_DIR . 'Core/Router.class.php';
include_once LIBS_DIR . 'Core/Utils.class.php';
include_once LIBS_DIR . 'Core/Doctrine.compiled.php';

//include_once LIBS_DIR . 'SSH.class.php';

// TODO: If No App => redirect 404
class Application {
    public function  __construct() {
        // On sauvegarde le timestamp actuel afin de calculer le temps d'exec de l'app
        $start_timestamp = microtime(true);

        $this->httpRequest = new HTTPRequest($this);
        $this->httpResponse = new HTTPResponse($this);
        
        // On récupère la config du projet
        $this->config = JSON::loadCfg(CFG_DIR . 'config.json');

        // On créer une connexion avec la bdd
        $this->initDB();
		
        // On instance le routeur afin de connaitre le nom des différents éléments
        // On lui passe en argument, la route par défaut si l'utilisateur n'a pas d'arguments GET
        $routerArgs = array(
            'defaultRoute' => $this->config['defaultRoute'], 
            'routesFile' => CFG_DIR . 'routes.json');
        $this->router = new Router($this, $routerArgs);
        $route = $this->router->getRoute(); // var_dump($route);
        
        if ($route == null) $this->httpResponse->redirect404();
        
        $this->name = $route['app'];
        $this->module = $route['module'];
        $this->action = $route['action'];

        // On charge les modèles
        Doctrine_Core::loadModels(LIBS_DIR . 'models/');

        // On inclut et on instancie le controlleur
        $ctrlerName = ucfirst($this->module) . 'Ctrler';
        include(APPS_DIR . $this->name . '/' . $ctrlerName . '.php');
        $ctrler = new $ctrlerName($this);
        
        // On récupère la page en exécutant l'action demandé
        $page = $ctrler->run($this->getAction(), $this->router->getVars());

        // Si le mode debug est activé, on transmet le temps d'exécution
        if ($this->config['debug']) {
            $execTime = microtime(true) - $start_timestamp;
            $page->setTplVars('footer', array('execTime' => $execTime));
        }

        $this->httpResponse->send($page);   
    }

    public function httpRequest() {
        return $this->httpRequest;
    }
    public function httpResponse() {
        return $this->httpResponse;
    }

    public function getName() {
        return $this->name;
    }
    public function setName($name) {
        $this->name = $name;
    }

    public function getModule() {
        return $this->module;
    }
    public function setModule($module) {
        $this->module = $module;
    }

    public function getAction() {
        return $this->action;
    }
    public function setAction($action) {
        $this->action = $action;
    }

    public function getDB() {
        if (!isset($this->db)) {
            $this->initDB();
        }

        return $this->db;
    }
    private function initDB() {
        $infosDB = $this->config['db'];
        $mgr = Doctrine_Manager::getInstance();

        if (isset($infosDB['prefixe'])) {
            $mgr->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT,
                $infosDB['prefixe'] . '_%s');
        }

        $mgr->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL);
        $mgr->setAttribute(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
        $mgr->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
        $mgr->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING,
            Doctrine_Core::MODEL_LOADING_CONSERVATIVE);

        $this->db = Doctrine_Manager::connection($infosDB['dsn']);
    } // TODO: Del config['db']['dsn'] ?

    public function getLang() {
        return $this->lang;
    }
    public function setLang($lang) {
        $this->lang = $lang;
    }
    
    // Getter magique permettant de récupérer un élément de la config
    public function __get($key) {
        return @$this->config[$key];
    }
    
    public function isDebug() {
        return $this->config['debug'];
    }

    private $httpRequest;
    private $httpResponse;
    private $router;
    private $page;

    private $name;
    private $module;
    private $action;
    private $lang;

    private $config;
}

// TODO: Recomment
abstract class ApplicationComponent {
    // Le premier argument des classes filles doit être l'instance de l'application instanciant
    // Tous les autres arguments fournies au constructeur seront transmis sous forme d'un unique array
    // A la méthode "init"
    public function __construct(Application $app, $args = array()) {
        $this->app = $app;
        $this->init($args);
    }

    protected function init() {}

    // Nécessaire pour accéder à l'attribut $app depuis les classes filles
    public function app() {
        return $this->app;
    }

    private $app;
}

abstract class BaseCtrler extends ApplicationComponent {
    protected function init() {
        $pageArgs = array();
        
        // On initialise la session
        $this->session = new Session('panel');
        
        // On récupère la langue par défaut et la langue de l'utilisateur
        $pageArgs = array(
            'defaultLang' => $this->app()->defaultLang, 
            'lang' => $this->session->lang, 
            'langs' => $this->app()->langs);
        
        // On instancie notre objet Page qui permet d'afficher des templates
        // Et contient le layout
        $this->page = new Page($this->app(), $pageArgs);
        $this->lang = $this->page->getLang();
    }
    public function run($action, $vars) {
        $method = 'run' . ucfirst($action);
        
        $this->page->addTpl('header', array(
            'connected' => $this->session->connected, 
            'pseudo' => $this->session->pseudo, 
            'isSU' => true, 
            'dpVer' => '0.4_design', 
            'lastDpVer' => '0.4', 
        ));  // On inclut le template contenant l'header
        // Ainsi que le fichier de traduction qui lui est associé
        $this->lang->loadTradFile('common/header');

        if ($this->session->connected) {
                $this->page->addCSS('main');
        }
        else {
                $this->page->addCSS('login');
        }
		
        // On exécute la méthode correspondant à l'action demandée
        if (empty($vars)) {
            $this->$method();
        }
        else {
            $this->$method($vars);
        }

        $this->page->addTpl('footer', array(
            'connected' => $this->session->connected));  // De même pour le footer
        return $this->page;
    }

    protected $page;
    protected $session;
    protected $lang;
}
?>
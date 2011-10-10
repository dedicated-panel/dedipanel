<?php
class Page extends ApplicationComponent {
    protected function init($args) {
        // On créer une instance de la classe langue afin d'avoir du multi-langue
        // Ayant une intégration facilité dans les templates
        $this->lang = new Lang($this->app(), $args);
        P::setInstance($this);
    }
    
    public function addTpl($tplName, $vars = array()) {
        // On refuse d'ajouter le template s'il n'existe pas
        if (!file_exists($this->getPath($tplName))) {
            throw new RuntimeException('Le template sélectionné n\'existe pas.');
        }
        
        $this->templates[] = $tplName;
        $this->vars[$tplName] = $vars;
        return true;
    }
    public function setTplVars($tplName, $vars) {
        $this->vars[$tplName] = $vars;
    }
    public function addTplVars($tplName, $vars) {
        $tplVars = &$this->vars[$tplName];
        
        if (!isset($tplVars)) $tplVars = $vars;
        else $tplVars = array_merge($tplVars, $vars);
    }
	
    public function addCSS($css) {
        $this->cssFiles[] = $css;
    }
    public function getCSSFiles() {
        return $this->cssFiles;
    }

    private function getPath($tplName) {
        return HTML_DIR . $tplName . '.html';
    }

    public function getLayout() {
        if (!isset($this->layout)) $this->generate();
        return $this->layout;
    }
    private function generate() {
        error_reporting(E_ALL ^ E_NOTICE);
        
        // On extrait les variables sauvegardés pour le template
        // Et on affiche ledit template
        foreach ($this->templates AS $tplName) {
            ob_start();
            
            extract($this->vars[$tplName]);
            include($this->getPath($tplName));
            
            $this->layout .= ob_get_clean();
        }
    }

    // Permet d'afficher un lien interne (ou externe) dans un template
    public static function a($text, $url, $vars = false, $trad = true, $baseUrl = BASE_URL) {
        // On supprime les slashs en trop et on en rajoute un manuellement à la fin
        // Cette procédure permet de garantir la présence des / nécessaires
        $url = trim($url, '/') . '/';

        // Si $vars est un array on les ajoutes les uns à la suite des autres avec des "/"
        // Sinon, on ne fait que la rajouter à la fin de l'url
        if (is_array($vars)) {
            foreach ($vars AS $val) {
                $url .= $val . '/';
            }
        }
        elseif ($vars != false) {
            $url .= $vars . '/';
        }
        
        if ($trad) $text = L::gT($text);
        echo '<a href="' , $baseUrl , $url , '">' , $text , '</a>';
    }
    
    // Permet d'afficher une image
    public static function getImg($src, $title = '', $tradTitle = true, $baseUrl = IMG_URL) {
        // On supprime les slashs en trop
        $baseUrl = rtrim($baseUrl, '/') . '/';
        
        if ($title != '' && $tradTitle) $title = L::eT($title);
        return '<img src="' . $baseUrl . $src . '" title="' . $title . '" />';
    }
    
    // Renvoie une instance de la classe "Lang"
    public function getLang() {
        return $this->lang;
    }
    
    private $templates = array();
    private $vars;
    private $layout;
    private $lang;
    private $cssFiles;
}

class P {
    private function __construct() {}
    
    // Permet de récupérer/modifier l'instance de la langue 
    // getInstance() renvoie null si l'instance n'est pas déclaré
    static public function getInstance() {
        return self::$instance;
        
    }
    static public function setInstance($instance) {
        self::$instance = $instance;
    }

    static public function a($text, $url, $vars = false, $trad = true, $baseUrl = BASE_URL) {
        if ($inst = self::getInstance()) {
            return $inst->a($text, $url, $vars, $trad, $baseUrl);
        }
        else return null;
    }
    static public function getImg($src, $title = '', $tradTitle = true, $baseUrl = IMG_URL) {
        if ($inst = self::getInstance()) {
            return $inst->getImg($src, $title, $tradTitle, $baseUrl);
        }
        else return null;
    }
    
    static $instance = null;
}

class Lang extends ApplicationComponent {
    protected function init($args) {
        $this->langs = explode(',', $args['langs']);
        
        // On vérifie que la langue usuelle soit correcte sinon on émet une erreur
        // (à moins qu'elle soit nulle)
        if (!empty($args['lang']) && !$this->isValidLang($args['lang'])) {
            throw new RuntimeException('Langue usuelle non valide.');
        }
        $this->lang = $args['lang'];
        
        // On ajoute une langue par défaut (si nécessaire et si elle est valide)
        if (!empty($args['defaultLang']) && $args['defaultLang'] != $this->lang
            && $this->isValidLang($args['defaultLang'])) {
            $this->defaultLang = $args['defaultLang'];
        }
        
        // On rend opérationnel "l'alias de la classe"
        L::setInstance($this);
    }
    
    // Cette méthode permet de vérifier si la langue $lang est disponible
    public function isValidLang($lang) {
        return in_array($lang, $this->langs);
    }
    // Cette méthode renvoie un array contenant les langues valides
    public function getValidLangs() {
        return $this->langs;
    }  
    // Récupère la langue actuelle
    
    public function getLang() { 
        return $this->lang;
    }  
    // Permet de modifier la langue utilisé
    // Et de recharger toutes les trads (si nécessaire)
    public function setLang($lang, $reloadAllFiles = false) {
        $oldLang = $this->lang;
        $this->lang = $lang;
        
        // On recharge toutes les traductions si nécessaire
        if ($reloadAllFiles != false && $oldLang != $lang) {
            $this->loadTraductions();
        }
    }
    
    // Permet d'ajouter le fichier de traduction $filename
    public function addTradFile($filename) {
        // On ajoute le fichier à la liste
        $this->files[] = $filename;
    }
    public function loadTradFile($filename) {
        // On ajoute le fichier à la liste
        $this->files[] = $filename;
    }
    
    // Cette méthode renvoie le chemin d'un fichier de traduction
    public static function getTradFilePath($filename, $lang) {
        return LANG_DIR . $filename . '.' . $lang . '.json';
    }
    // Cette méthode charge l'ensemble des fichiers de traductions
    private function loadTraductions() {
        $globalTrads = array();
        $files = $this->files;
        $defaultLang = $this->defaultLang; $userLang = $this->lang;
        
        foreach ($files AS $file) {
            $trads = array();
            
            if (!empty($defaultLang)) {
                $defaultLangTrads = JSON::loadCfg($this->getTradFilePath($file, $defaultLang));
                if ($defaultLangTrads != false) {
                    $trads = $defaultLangTrads;
                }
            }
            if (!empty($userLang)) {
                $userLangTrads = JSON::loadCfg($this->getTradFilePath($file, $userLang));
                if ($userLangTrads != false) {
//                    var_dump($trads, $userLangTrads);
                    $trads = array_merge($trads, $userLangTrads);
//                    var_dump($trads);
                }
            }
            
            $globalTrads = array_merge($globalTrads, $trads);
        }
        
        $this->trads = $globalTrads;
    }
    
    // Permet de récupérer une traduction selon la clé fourni
    public function getTraduction($key) {
        // On vérifie qu'il y ai bien des traductions en mémoire
        // Si ce n'est pas le cas et qu'il y a des fichiers à charger, on charge les trads
        // S'il n'y a aucun fichier à charger on affiche un message d'erreur
        if (empty($this->trads)) {
            if (empty($this->files)) {
                throw new RuntimeException('Aucun fichier de traduction n\'a été indiqué.');
            }
            else $this->loadTraductions();
        }
        
        if (array_key_exists($key, $this->trads)) {
            return $this->trads[$key];
        }
        else return $key;
    }
    
    public function setCurrentFile() { return true; }
    
    // Cette méthode permet de récupérer une traduction précise
    // Dans le fichier et selon le type précisé
    private function getSpecificTrad($key, $trads, $type = Lang::USER_LANG) {
        // Si $trads est un array, c'est qu'il s'agit d'un tableau de traduction
        if (is_array($trads)) {
            // On vérifie si une traduction correspond à la clé et on la renvoie (si nécessaire)
            if (!empty($trads) && isset($trads[$type][$key])) {
                return $trads[$type][$key];
            }
            
            // Si rien n'a été trouvé 
            // Et que la recherche actuelle se fait sur la langue utilisateur
            // On refait une recherche sur la langue par défaut (si nécessaire)
            if ($type == Lang::USER_LANG) {
                return $this->getSpecificTrad($key, $trads, Lang::DEFAULT_LANG);
            }
            else return false;
        }
        // Si c'est une chaîne, c'est qu'on cherche $key dans le fichier $trads
        elseif (is_string($trads)) {
            $trads = $this->getSpecificTrad($key, $this->trads[$trads], $type);
            return (!empty($trads) ? $trads : null);
            
        }
        else return false;
    }
    
    // Contient la langue par défaut à utilisé
    // Et la langue usuelle de l'utilisateur
    private $defaultLang = null;
    private $lang = null;
    
    // Arrays contenant les différentes traductions
    // Ainsi que les différents fichiers utilisés
    private $trads = array();
    private $files = array();
    
    private $langs; // Array des langues disponible
    
    const USER_LANG    = 0x0;
    const DEFAULT_LANG = 0x1;
}

// Cette classe est un "alias" de la classe Lang
// Elle permet d'avoir une syntaxe plus simple lors de l'écriture des templates
class L {
    private function __construct() {}
    
    // Permet de récupérer/modifier l'instance de la langue 
    // getInstance() renvoie null si l'instance n'est pas déclaré
    static public function getInstance() {
        return self::$instance;
        
    }
    static public function setInstance($instance) {
        self::$instance = $instance;
    }
    
    // Cette méthode permet de modifier le fichier "de référence" actuelle
    // Elle interragit avec l'instance (ou false si celle-c n'est pas déclaré)
    // L::sCF est un raccourci à utilisé dans les templates
    static public function setCurrentFile($file) {
        if (self::getInstance() != null) {
            self::getInstance()->setCurrentFile($file);
        }
        else return false;
    }
    static public function sCF($file) {
        return self::setCurrentFile($file);
    }
    
    // Cette méthode va interragir avec l'instance de Lang (ou null si non déclaré)
    // Afin de renvoyer la traduction correspondant à $key
    // Cette traduction est renvoyé, 
    // Ou celle par défaut si nécessaire, ou false si aucune des deux
    // L::gT($key) est quant à elle un alias de "getTraduction"
    static public function getTraduction($key) {
        if (self::getInstance() != null) {
            return self::getInstance()->getTraduction($key);
        }
        else return $key;
    }
    static public function gT($key) {
        $trad = self::getTraduction($key);
        return $trad;
    }
    static public function eT($key) {
        echo self::getTraduction($key);
    }
    
    static $instance = null;
}
?>
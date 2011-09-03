<?php
class Page extends ApplicationComponent {
    protected function init($args) {
        // On créer une instance de la classe langue afin d'avoir du multi-langue
        // Ayant une intégration facilité dans les templates
        $this->lang = new Lang($this->app(), $args);
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
    public static function a($text, $url, $vars = false, $baseUrl = BASE_URL) {
        // On supprime les slashs en trop et on en rajoute un manuellement à la fin
        // Cette procédure permet de garantir la présence des / nécessaires
        $baseUrl = rtrim($baseUrl, '/') . '/';
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
        
        echo '<a href="' , $baseUrl , $url , '">' , L::eT($text) , '</a>';
    }

    // Renvoie une instance de la classe "Lang"
    public function getLang() {
        return $this->lang;
    }
    
    private $templates = array();
    private $vars;
    private $layout;
    private $lang;
}

class Lang extends ApplicationComponent {
    protected function init($args) {
        $this->lang = $args['lang'];
        
        // On ajoute une langue par défaut (si nécessaire)
        if (!empty($args['defaultLang']) && $args['defaultLang'] != $this->lang) {
            $this->defaultLang = $args['defaultLang'];
        }
        
        // On rend opérationnel "l'alias de la classe"
        L::setInstance($this);
    }
    
    // $traductions doit être un array comprenant les clé de traductions ainsi que leur valeur
    public function loadTraductionFile($filename) {        
        // On charge le fichier de traduction correspondant à la langue de l'utilisateur
        // Et on les ajoute à celles prises en charges (à moins que le fichier n'existe pas)
        if (!empty($this->lang)) $this->addTradFile($filename, $this->lang);
        
        // On fait de même pour la langue par défaut
        // Sauf si celle la même que la langue déjà chargé ou que le fichier n'existe pas
        if (!empty($this->defaultLang) && $this->lang != $this->defaultLang) {
            $this->addTradFile($filename, $this->defaultLang, Lang::DEFAULT_LANG);
        }
        
        // Si aucun fichier n'est actuellement préselectionné, on indique celui-ci
        if (empty($this->currentFile)) {
            $this->currentFile = $filename;
        }
    }
    
    // On ajoute les traductions d'un fichier donné 
    // Pour une langue et un type de traduction particulier
    private function addTradFile($filename, $lang, $type = Lang::USER_LANG) {
        $trads = JSON::loadCfg($this->getTradFilePath($filename, $lang));
        
        if ($trads != false) {
            $this->trads[$filename][$type] = $trads;
            $this->files[] = $filename;
            return true;
        }
        else return false;
    }
    
    // Cette méthode renvoie le chemin d'un fichier de traduction
    public static function getTradFilePath($filename, $lang) {
        return ROOT_DIR . '/' . $filename . '.' . $lang . '.json';
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
            foreach ($this->trads[Lang::USER_LANG] AS $file => &$cont) {
                // On charge le fichier de traduction correspondant à la nouvelle langue
                // Puis on supprime celui correspondant à l'ancienne langue
                $this->addTradFile($file, $lang, Lang::USER_LANG);
                
                // On récupère le nom de l'ancien fichier
                $oldFilename = $this->getTradFilePath($file, $oldLang);
                unset($oldFilename);
            }
        }
    }
    
    // Permet de modifier le fichier de traduction à inspecter en priorité
    public function setCurrentFile($file) {
        $this->currentFile = $file;
    }
    
    // Permet de récupérer une traduction selon la clé fourni
    public function getTraduction($key) {
        // On vérifie qu'il y ait bien des traductions de sélectionnés
        if (empty($this->trads)) {
            return 'Aucune traduction sélectionné.';
        }
        
        // On vérifie les traductions disponibles dans le fichier "prioritaire"
        if (isset($this->currentFile)) {
            $trad = $this->getSpecificTrad($key, $this->currentFile);
            if ($trad != false) return $trad;
        }
        
        // Puis on vérifie tous les fichiers actuellement chargé
        foreach ($this->files AS $file) {
            $trad = $this->getSpecificTrad($key, $file);
            if ($trad != false) return $trad;
        }
        
        // Finallement on retourne null (sauf si qql chose à été trouvé entre temps)
        return null;
    }
    
    // Cette méthode permet de récupérer une traduction précise
    // Dans le fichier et selon le type précisé
    private function getSpecificTrad($key, $trads, $type = Lang::USER_LANG) {
        // Si $file est un array, c'est qu'il s'agit d'un tableau de traduction
        // Si c'est une chaîne, on charge le tableau de traduction voulu
        if ( is_array($trads)) {
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
        elseif (is_string($trads)) {
            $trads = $this->getSpecificTrad($key, $this->trads[$trads], $type);
            return (!empty($trads) ? $trads : null);
            
        }
        else return false;
    }
    
    private $defaultLang = null;
    private $lang;
    
    private $trads = array();
    private $files = array();
    
    private $currentFile;
    
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
        else return null;
    }
    static public function gT($key) {
        return self::getTraduction($key);
    }
    static public function eT($key) {
        $trad = self::getTraduction($key);
        echo ($trad != null) ? $trad : $key;
    }
    
    static $instance = null;
}
?>
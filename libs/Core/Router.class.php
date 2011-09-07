<?php
// TODO: Laisser libre le placement des arguments dans la variable "vars" des routes

// Le Routeur permet de récupérer les informations sur la page à récupérer selon l'url rentré
// Et selon le fichier routes.json
 class Router extends ApplicationComponent {
    // Cette méthode est exécuté par le constructeur de la classe qui transmet :
    // La route par défaut ($defaultRoute) ainsi que le fichier à charger ($routesFile)
    protected function init($args) {
//        var_dump($args);
        // On extrait les infos renvoié par le constructeur parent
        extract($args);
        
        // On récupère les arguments GET de l'application
        // On supprime le dernier slash présent si nécessaire
        $getArgs = HTTPRequest::getData(INPUT_GET);
        if (@strrpos($getArgs, '/') == strlen($getArgs)-1) {
            $getArgs = substr($getArgs, 0, -1);
        }

        if (empty($getArgs)) $getArgs = $defaultRoute;
        
        $this->loadRoutesFile($routesFile);
        $this->routingArgs = $getArgs;
    }
    
    // Permet de charger un fichier de routage autre (remet à 0 les infos sur la route actuelle)
    public function loadRoutesFile($routesFile) {
        $this->routes = JSON::loadCfg($routesFile);
        $this->route = null;
    }
    
    // Cette méthode permet de renvoyer les infos concernant l'app (selon la route)
    public function getRoute() {
        // On récupère les-dites infos si ce n'est pas encore fait
        if (!isset($this->route)) {
            $this->analyzeRoute($this->routes);
        }
        return $this->route;
    }
    
    private function analyzeRoute($routes) {
        $ret = array();

        // On vérifie les routes passées en argument
        // $url correspondant étant le pattern utilisé pour vérifié la route (il est transformé)
        // $infos contient l'array correspondant au pattern
        foreach ($routes AS $url => $infos) {
            $matches = false; $varsName = array();

            // Si des variables sont précisés dans la config de la route,
            // On remplace les id des vars par leur valeur dans l'url
            if (isset($infos['vars'])) {
                $vars = $infos['vars'];
                
                foreach ($vars AS $nom => $val) {
                    $nb = 0;
                    $url = str_replace('$$' . $nom, '(' . $val . ')', $url, $nb);
                    
                    if ($nb > 0) {
                        $varsName[] = $nom;
                    }
                }
            }
            
            // On arrête le tour de boucle si le pattern ne correspond pas
            if (!preg_match("#$url#", $this->routingArgs, $matches)) continue;

            // On enlève la première entrée de $matches qui contient l'ensemble du masque
            array_shift($matches);

            // Si on a utilisé des variables pour le pattern, on les extraits
            // Afin quelles soient transmises au ctrler
            if ($matches != false) {
                $retVars = array();
                foreach ($varsName AS $key => $varname) {
                    $retVars[$varname] = $matches[$key];
                }
                
                $this->vars = $retVars;
            }
            
            // S'il y a une route d'indiqué, on la sauvegarde
            // Elle sera utilisé par défaut s'il n'y en a pas d'autre de trouvé
            // Dans les routes filles
            if (isset($infos['route'])) {
                $route = explode('/', $infos['route']);
                
                $this->route['app'] = $route[0];
                $this->route['module'] = $route[1];
                $this->route['action'] = (isset($route[2]) ? $route[2] : '');
            }
            // Si une action est précisé, on modifie celle sauvegardé
            if (isset($infos['action'])) {
                $this->route['action'] = $infos['action'];
            }
            // Si des routes filles existes, on les analyses
            if (isset($infos['routes'])) {
                $this->analyzeRoute($infos['routes']);
            }

            // On a trouvé notre route, on sort de la boucle
            break;
        }
    }
    
    public function getVars() {
        return $this->vars;
    }
    
    private $route;
    private $routes;
    private $routingArgs;
    private $vars = null;
}
?>
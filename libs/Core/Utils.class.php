<?php
define('FILTER_PASSWORD', 1000);

define('FIELD_TEXT',  0xF0);
define('FIELD_MDP',   0xF1);
define('FIELD_EMAIL', 0xF2);
define('FIELD_PORT',  0xF3);
define('FIELD_IP', 0xF4);
define('FIELD_BOOL', 0xF5);

define('BUTTON_SEND',  0xB1);
define('BUTTON_RESET', 0xB2);

class JSON {
    public function __construct($filename = '') {
        $this->filename = $filename;
    }
    
    // Cette méthode n'a besoin de son argument que si elle est appelé statiquement
    public static function loadCfg($filename = null) {
        // On vérifie que le fichier demandé existe
        if (!file_exists($filename)) return false;
        
        // On instancie la classe si la méthode pas été appelé statiquementsn
        $file = file_get_contents($filename);
        
        // On vérifie que le fichier ce soit bien chargé sinon on renvoie false
        if ($file == false) return false;
        
        // On décode le fichier json
        $json = json_decode($file, true);

        // On affiche un message d'erreur si le fichier JSON n'est pas vide
        // Mais que le résultat du parssage soit nul
        if ($json == null && $file != null) echo JSON::lastError();

        return $json;
    }

    // JSON ayant une syntaxe assez stricte (utilisation des dble quotes, des {})
    // On va l'étendre un peu de sorte à faciliter l'utilisation des fichiers JSON
    // @input   $json   string    Donnée(s) au format JSON
    // @return          string    Donnée(s) au format JSON après traitement
    public static function checkSyntax($json) {
        // On autorise les simple quotes
        $json = str_replace('\'', '"', $json);
        // Ainsi que la représentation de 
    }

    public static function lastError($filename = null) {
        $erreur = null;

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $erreur = 'Aucune erreur.';
                break;
            case JSON_ERROR_DEPTH:
                $erreur = 'Profondeur maximale atteinte.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $erreur = 'Inadéquation des modes ou underflow.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $erreur = 'Erreur lors du contrôle des caractères.';
                break;
            case JSON_ERROR_SYNTAX:
                $erreur = 'Erreur de syntaxe; JSON malformé.';
                break;
            case JSON_ERROR_UTF8:
                $erreur = 'Caractère(s) UTF-8 malformé(s) ou erreur d\'encodage.';
                break;
            default:
                $erreur = 'Erreur inconnue.';
                break;
        }
        
        // On inclut le nom du fichier (si spécifié)
        if ($filename != null) $erreur .= 'Fichier: ' . $filename;

        return $erreur;
    }

    public function write($content) { echo 'TODO: JSON::write($content)<br />'; }

    private $filename;
    private $content;
    private $instance;
}

class Form {
    // Le constructeur de la classe permet de définir les champs et les bouttons du formulaire
    public function __construct($fields, $buttons) {
        $this->fields = $fields;
        
        if (is_string($buttons)) {
            $buttons = array($buttons => BUTTON_SEND);
        }
        
        $this->buttons = $buttons;
    }

    public function getView() {
        if (!isset($this->view)) {
            $this->generateView();
        }
        
        return $this->view;
    }
    
    private function generateView() {
        $view = '';
        
        foreach ($this->fields AS $name => $infos) {
            $type = $infos['type']; $value = (isset($infos['value']) ? $infos['value'] : '');
            $label = '<label for="' . $name . '">' . $infos['desc'] . ' :</label> ';
            
            if ($type == FIELD_TEXT) {
                $field = '<input type="text" name="' . $name . '" 
                    id="' . $name . '" value="' . $value . '" ';
            }
        }
    }
    
    public function setFields($fields) {
        $this->fields = $fields;
        $this->view = null;
    }
    
    public static function hasSend($submitFieldName = 'envoi') {
        return isset($_POST[$submitFieldName]);
    }

    // Cette méthode statique permet de vérifier un formulaire selon les filtres définit
    public static function verifyData($fields) {        
        $filter_text = array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_NO_ENCODE_QUOTES);
        $filter_port = array('filter' => FILTER_VALIDATE_INT, 'options' => array('min_range' => 0, 'max_range' => 65536));
        $filter_bool = array('filter' => FILTER_VALIDATE_INT, 'options' => array('min_range' => 0, 'max_range' => 1));
        
        $filters = array(
            FIELD_TEXT => $filter_text, FIELD_MDP => $filter_text, 
            FIELD_EMAIL => FILTER_VALIDATE_EMAIL, FIELD_PORT => $filter_port, 
            FIELD_IP => FILTER_VALIDATE_IP, 
            FIELD_BOOL => $filter_bool);
        
        $filterOptions = array();
        $rewrite = array();
        
        // On ajoute chaque itération de l'array dans celui des options de filtrages
        foreach ($fields AS $fieldName => $type) {
            // Liste des options actuelles pour le champ définit
            $options = array();
            
            // Si $type est un array, on le traite pour récupérer toutes les infos
            if (is_array($type)) {
                // On convertit le type de champ (si celui-ci existe)
                $internFilterType = @$fiters[$type['type']];
                if (isset($internFilterType)) {
                    $options += $internFilterType;
                    unset($type['type']);
                }
                
                // On vérifie s'il y a un champ fieldName dans l'array $type
                // Si c'est le cas on doit réécrire l'array donné par le filtrage
                if (isset($type['fieldName'])) {
                    $idForm = $type['fieldName'];
                    $rewrite[$idForm] = $fieldName;
                    $fieldName = $idForm;
                }
                
                // On fusionne nos deux tableaux d'options 
                // Dans le tableau final utilisé pour filtrer
                $filterOptions[$fieldName] = array_merge($options, $type);
            }
            // Sinon, on ajoute une entrée à l'array $filterOptions contenant, 
            // Comme clé le nom du champ, comme valeur le type convertit
            else $filterOptions[$fieldName] = $filters[$type];
        }
        
        // On filtres les données
        $filtres = filter_input_array(INPUT_POST, $filterOptions);
        
        // On réécrit l'array retourné par le filtrage (si nécessaire)
        foreach ($rewrite AS $idForm => $fieldName) {
            $filtres[$fieldName] = $filtres[$idForm];
            unset($filtres[$idForm]);
        }
        
        // On vérifie qu'il n'y ai pas d'erreur dans l'array filtré
        $erreurs = self::getErrors($filtres);
        $erreurs = (!empty($erreurs)) ? $erreurs : false;
        
        // On supprimes les variables qui ont posés problèmes
        if ($erreurs != false) {
            foreach ($erreurs AS $err => $val) {
                unset($filtres[$err]);
            }
        }
        
        // On renvoie un array contenant 2 valeures : le nb d'erreurs & les données filtrés
        return array(($erreurs == false ? array() : $erreurs), $filtres);
    }
    
    public static function getErrors($vars) {
        $errors = array();

        foreach ($vars AS $varname => $var) {
            if ($var === false || $var === null) {
                $errors[] = $varname;
            }
        }

        return $errors;
    }

    public static function radioIsChecked($field) {
        $radio = filter_input(INPUT_POST, $field, FILTER_VALIDATE_INT);
        return ($radio == 1) ? true : false;
    }
    
    private $fields;
    private $buttons;
    
}

// Cette classe permet de gérer et accéder facilement aux variables d'une session
class Session {
    public function __construct($name, $lifeTime = 1800) {
        $this->name = $name;
        $this->lifeTime = $lifeTime;
        
        // On démarre la session
        $this->start();
        
//        var_dump($lifeTime, $this->sess['updated_at'], time(), 
//            time() - $this->sess['updated_at'] > $lifeTime);
        
        // Puis on vérifie si celle-ci est périmé
        // Pour la redémarrer si nécessaire
        if (isset($this->sess['updated_at']) && 
            (time() - $this->sess['updated_at'] > $lifeTime)) {
//            echo 'destroy&start';
            $this->destroy();
            $this->start();
        }
    }
    
    // Le destructeur permet de sauvegarder les données
    // Ainsi que la date de maj qui correspond au time() actuel
    public function __destruct() {
        $this->sess['updated_at'] = time();
        $_SESSION[$this->name] = $this->sess;
    }
    
    // Permet d'activer le système de session
    private function start() {
        // Nom de la session
        $name = $this->name;
        
        // On intialise le système de session
        session_start();
        
        // On préfère utiliser $_SESSION[$this->name] à la place de session_name()
        // Qui bouffe pas mal de ressources
        if (!isset($_SESSION[$name])) $_SESSION[$name] = array();
        
        $this->sess = $_SESSION[$name];
//        var_dump('start', $this->sess);
    }
    // Permet de détruire proprement la session
    public function destroy() {
        // On détruit les données de la session
        $this->sess = array();
        $_SESSION[$this->name] = array();
        
        // Puis on détruit la session en totalité
        session_unset();
        session_destroy();
    }
    
    // Getter & Setter magique
    public function __get($k) {
        return @$this->sess[$k];
    }
    public function __set($k, $v) {
        $this->sess[$k] = $v;
    }
    
    private $name;
    private $sess;
    private $lifeTime;
}

/*class Form {
    private function __construct() {}

    public static function verif($input, $vars) {
        $erreurs = filter_input_array()
        $ret = filter_input_array($input, $vars);
        var_dump($ret);
        
        foreach ($vars AS $var => &$filtreOpt) {
            if (is_array($filtreOpt)) {
                if ($filtreOpt['filter'] == FILTER_PASSWORD) {
                    $filtreOpt['filter'] = FILTER_SANITIZE_STRING;
                    $filtreOpt['flags'] = FILTER_FLAG_NO_ENCODE_QUOTES;
                }

                if ($filtreOpt['filter'] == )
            }
            else {
                
            }

            if ($filtre == FILTER_PASSWORD) {
                
            }
        }
    }
}*/
?>
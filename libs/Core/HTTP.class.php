<?php
class HTTPRequest extends ApplicationComponent {
	// Cette méthode permet de récupérer des données get (INPUT_GET) ou POST (INPUT_POST) 
    public static function getData($type) {
        // On vérifie le type de donnée qu'on souhaite récupérer
		// Si ce n'est pas un type valide, la méthode renvoie null
        $type = ($type == INPUT_GET || $type == INPUT_POST) ? $type : null;
		if ($type == null) return null;
		
        // Si les arguments souhaités on déjà été récupéré, on les renvoie
        if (self::$inputArgs[$type] != null) return self::$inputArgs[$type];

		// On récupère les données 
		$data = '';
		if 		($type == INPUT_POST) 	$data = $_POST;
		elseif 	($type == INPUT_GET && isset($_GET['args'])) $data = $_GET['args'];

        // On les sauvegarde pour y accéder plus facilement la prochaine fois
		self::$inputArgs[$type] = $data;
		// Et on fini par renvoyer les dites données
        return $data;
    }

    private static $inputArgs;
}

class HTTPResponse extends ApplicationComponent {
    protected function init() {
        // On démarre la temporisation de sortie
        ob_start();
    }

    public function setHeader($name, $val) {
        header($name . ': ' . $val);
    }

    public function redirect($route, $baseUrl = BASE_URL) {
        if ($baseUrl == BASE_URL) $baseUrl .= '/';

        $this->setHeader('location', $baseUrl . $route);
        exit();
    }

    public function redirect404() {
        echo '404'; exit();
    }

    public function send(Page $page) {
        /*if ($this->app->JSONResp) {
            $this->setHeader('Content-Type', 'application/json');
        }*/

        $layout = $page->getLayout();
        if (!$this->app()->isDebug()) {
            ob_end_clean();
            exit($layout);
        }
        else {
            echo $layout;
            ob_end_flush();
        }
    }
}
?>
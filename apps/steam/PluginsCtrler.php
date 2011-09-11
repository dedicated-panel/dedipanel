<?php
// TODO: Limitation accès plugins

class PluginsCtrler extends BaseCtrler {
    // On réécrit la fonction lancant les méthodes correspondant à l'action
    // Afin de s'assurer que ce controller n'est appelé qu'avec les droits nécessaires
    public function run($action, $vars) {
        // On vérifie que la session est active, sinon on redirige l'utilisateur
        if (!$this->session->connected) {
            $this->app()->httpResponse()->redirect('steam/show');
        }
        
        // On vérifie que le serveur appartient à l'utilisateur
        $uid = $this->session->uid;
        $serv = Doctrine_Core::getTable('Steam')->findByIdAndUid($vars['id'], $uid);
        $serv = $serv->getFirst();
        
        if (!$serv) $this->app()->httpResponse()->redirect('steam/show');
        $vars['serv'] = $serv;

        return parent::run($action, $vars);
    }

    protected function runInstall($vars) {
        $plugin = $vars['plugin']; $serv = $vars['serv'];
        
//        $dependances = array();
//        $this->page->addTpl('plugins/install');

        // On vérifie que le plugin choisit soit compatible avec le mod du jeu
        if (($plugin == 'amx' || $plugin == 'amxx' || $plugin = 'mm')
            && $serv->Jeu->source != 0) {
            $this->app()->httpResponse()->redirect('steam/show');
        }
        elseif (($plugin == 'mmsrc' || $plugin == 'srcm' || $plugin == 'es') 
                && $serv->Jeu->source == 0) {
            $this->app()->httpRespone()->redirect('steam/show');
        }

        $gameDir = $serv->getGameDir();
        $scriptFile = $gameDir . 'plugins.sh';
        $logFile = $gameDir . 'plugins.log';

        // On uplaod le script permettant d'installer un plugin
        $ssh = SSH::get($serv->Vm->ip, $serv->Vm->port, $serv->Vm->user, $serv->Vm->keyfile, true);
        $ssh->putFile(CFG_DIR . 'sh/plugins.sh', $scriptFile);

        // On donne les droits d'exécution sur le fichier
        $ssh->exec('chmod +x ' . $scriptFile);

        // Puis on lance un nohup pour installer le plugin
        $cmd  = 'cd ' . $gameDir . ' && nohup ' . $scriptFile . ' ' . $plugin;
        $cmd .= ' > ' . $logFile . ' 2>&1 &';
        $install = $ssh->exec($cmd);
//        var_dump($cmd, $ssh->getLog());

        $serv->$plugin = true;
        $serv->save();

        $this->app()->httpResponse()->redirect('steam/show');
    }
}
?>
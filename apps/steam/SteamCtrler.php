<?php
class SteamCtrler extends BaseCtrler {
    // On réécrit la fonction lancant les méthodes correspondant à l'action
    // Afin de s'assurer que ce controller n'est appelé qu'avec les droits nécessaires
    protected function init() {
        parent::init();
        
        if (!$this->session->connected) {
            $this->app()->httpResponse()->redirect('utilisateur/login');
        }
        
        // On charge le fichier de traduction approprié
        $this->lang->loadTradFile('steam/steam');
    }

    protected function runShow() {
        require_once(LIBS_DIR . 'Steam/Server.class.php');
        $serveurs = Doctrine_Core::getTable('Steam')->findAll();
        
        $this->page->addTpl('steam/show', array('serveurs' => $serveurs));
    }

    // Cette méthode permet d'ajouter un serveur
    protected function runAdd() {        
        $erreurs = array(); $form = array();
        $valid = null; $exist = null;
        
        if (Form::hasSend()) {
            list($erreurs, $form) = Form::verifyData(array(
                'idVm' => array('fieldName' => 'vm', 'type' => FILTER_VALIDATE_INT),
                'port' => FIELD_PORT, 
                'jeu' => array('type' => FILTER_VALIDATE_INT),
                'dir' => FIELD_TEXT,
                'maxplayers' => array(
                    'type' => FILTER_VALIDATE_INT, 
                    'options' => array('min_range' => 1, 'max_range' => 99)
                ))
            );
            $alreadyInstalled = Form::radioIsChecked('exists');

            if (!$erreurs) {
                $table = Doctrine_Core::getTable('Steam');
                $exists = $table->exists($form['idVm'], $form['port'], $form['dir']);

                // On vérifie que les infos passés à Doctrine soit correct
                // Si c'est le cas, on installe le serveur & on enregistre la ligne
                // Sinon, une message d'erreur s'affiche (via addTplVars ci-dessus)
                if (!$exists) {
                    $serv = new Steam();
                    $serv->fromArray($form);
                    $serv->save();
                    
                    // On lance l'installation (si nécessaire)
                    // Et on upload les scripts du panel
                    if (!$alreadyInstalled) $serv->installServer();
                    $serv->putHldsScript();
                    
                    $this->app()->httpResponse()->redirect('steam');
                }
                else $erreurs['exists'] = true;
            }
        }
        
        $vms = Doctrine_Core::getTable('Vm')->getVMs();
        $jeux = Doctrine_Core::getTable('Jeu')->getAvailableGames();

        $this->page->addTpl('steam/add', array(
            'form' => $form, 'erreurs' => $erreurs, 
            'VMs' => $vms, 'jeux' => $jeux));
    }

    // Cette méthode permet de modifier un serveur
    protected function runEdit($vars) {
        $id = $vars['id'];
        $steamTable = Doctrine_Core::getTable('Steam');

        if ($serv = $steamTable->find($id, Doctrine_Core::HYDRATE_ARRAY)) {
            $vm = Doctrine_Core::getTable('Vm')->find($serv['idVm'], Doctrine_Core::HYDRATE_ARRAY);
            $this->page->addTpl('steam/edit', array('serv' => $serv, 'vm' => $vm));
            
            if (Form::hasSend()) {
                list($erreurs, $form) = Form::verifyData(array(
                    'port' => FIELD_PORT, 
                    'jeu' => array('type' => FILTER_VALIDATE_INT),
                    'dir' => FIELD_TEXT,
                    'maxplayers' => array(
                        'type' => FILTER_VALIDATE_INT, 
                        'options' => array('min_range' => 1, 'max_range' => 99))
                ));

                if (!$erreurs) {
                    $regenScript = false; $install = false; $move = false;
                    // On vérifie quelles données ont changés pour faire le nécessaire sur le serv
                    if ($form['port'] != $serv['port'] ||
                        $form['maxplayers'] != $serv['maxplayers']) {
                        $regenScript = true;
                    }
                    elseif ($form['jeu'] != $serv['jeu']) {
                        $install = true;
                        $regenScript = true;
                    }
                    elseif ($form['dir'] != $serv['dir']) {
                        $move = $serv['dir'];
                        $regenScript = true;
                    }
                    
                    // On modifie les données selon le formulaire
                    // Et on vérifie qu'elles sont acceptés par Doctrine
                    $serv = Doctrine_Core::getTable('Steam')->find($id);
                    $serv->fromArray($form);
                    $valid = $serv->isValid();

                    $this->page->addTplVars('steam/edit', array('valid' => $valid));
                    
                    if ($valid) {
                        // On modifie le serveur en conséquence
                        if ($install) {
                            $serv->installServer();
                        }
                        if ($move != false) {
                            $oldDir = $serv->getServDir($move);
                            $newDir = $serv->getServDir();

                            $ssh = SSH::get($serv->Vm->ip, $serv->Vm->port,
                                $serv->Vm->user, $serv->Vm->keyfile);
                            $ret = $ssh->exec('mv ' . $oldDir . ' ' . $newDir);
                        }
                        
                        if ($regenScript) {
                            $serv->putHldsScript();
                        }
                        
                        // On sauvegarde les changements dans la bdd et on redirige
                        $serv->save();
//                        $this->app()->httpResponse()->redirect('steam');
                    }
                    else $erreurs['valid'] = true;
                }
                
                // On ajoute la liste d'erreurs au template
                $this->page->addTplVars('steam/edit', array('erreurs' => $erreurs));
            }
            
            // On récupère la liste des jeux disponibles
            // Et on l'ajoute au template
            $jeux = Doctrine_Core::getTable('Jeu')->getAvailableGames();
            $this->page->addTplVars('steam/edit', array('jeux' => $jeux));
        }
        else $this->app()->httpResponse()->redirect('steam/show');    
    }

    // Celle-ci permet de supprimer le serveur désigné
    // On le supprime également de la machine
    protected function runDel($vars) {
        $id = $vars['id']; $uid = $this->session->uid;
        $steamTable = Doctrine_Core::getTable('Steam');
        // On fait une suppression "soft" par défaut, "hard" si précisé tel quel
        $mode = ($vars['mode'] == 'hard') ? 'hard' : 'soft';
        
        if ($serv = $steamTable->find($id))  {
            // On supprime le serveur de la bdd
            $serv->delete();
            
            // On le supprime également de la VM (si hard del souhaité)
            if ($mode == 'hard') {
                $vm = $serv->Vm;

                // On instancie une connexion ssh
                // On arrête le serveur et on supprime le dossier
                $ssh = SSH::get($vm->ip, $vm->port, $vm->user, $vm->keyfile, true);
                
                $binPath = $serv->getBinDir() . 'hlds.sh';
                $ssh->exec('if [ -e ' . $binPath . ' ]; ' . $binPath . ' stop; fi');
                $ssh->exec('rm -Rf ' . $serv->getServDir());
            }
        }

        $this->app()->httpResponse()->redirect('steam/show');
    }

    // Cettet méthode permet de démarrer/arrêter/redémarrer un serveur
    protected function runState($vars) {
        $id = $vars['id'];

        if ($serv = Doctrine_Core::getTable('Steam')->findById($id)) {
            $serv = $serv[0]; $vm = $serv->Vm;

            // On démarre une connexion ssh et on exécute la commande
            $ssh = SSH::get($vm->ip, $vm->port, $vm->user, $vm->keyfile, true);
            $ssh->exec('cd ' . $serv->getBinDir() . ' && ./hlds.sh ' . $vars['state']);
        }

        $this->app()->httpResponse()->redirect('steam/show');
    }

    // Permet de régénérer le script shell utilisé par le panel
    protected function runRegenConfig($vars) {
        $id = $vars['id'];
        $regen = false;

        if ($serv = Doctrine_Core::getTable('Steam')->findById($id)) {
            $regen = $serv[0]->putHldsScript();
        }
        
        $this->page->addTpl('steam/regenConfig', array('regen' => $regen));
    }
}
?>
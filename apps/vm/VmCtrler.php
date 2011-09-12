<?php
require_once (LIBS_DIR . 'SSH.class.php');

class VmCtrler extends BaseCtrler {
    // On réécrit la fonction lancant les méthodes correspondant à l'action
    // Afin de s'assurer que ce controller n'est appelé qu'avec les droits nécessaires
    protected function init() {
        parent::init();
        
        if (!$this->session->connected) {
            $this->app()->httpResponse()->redirect('utilisateur/login');
        }
        
        // On charge le fichier de traduction
        $this->lang->loadTradFile('vm/vm');
    }

    // Permet de visualiser les VM prises en charge
    protected function runShow() {
        $this->page->addTpl('vm/show', array('vms' => Doctrine_Core::getTable('Vm')->findAll()));
    }

    // Permet d'ajouter une VM au panel
    protected function runAdd() {
        $form = array(); $erreurs = array();
        
        // On traite le formulaire si celui-ci a été transmis
        if (Form::hasSend()) {
            list($erreurs, $form) = Form::verifyData(array(
                'ip' => FIELD_IP, 
                'port' => FIELD_PORT,
                'user' => FIELD_TEXT,
                'mdp' => FIELD_TEXT));

            if (!$erreurs) {
                // On vérifie que cette VM et cet user ne soit pas déjà pris en charge
                $table = Doctrine_Core::getTable('Vm');
                $exists = $table->exists($form['ip'], $form['port'], $form['user']);

                // On vérifie ensuite que les identifiants ssh fournies soient corrects
                if ($exists == false) {
                    extract($form);
                    $ssh = SSH::isValidIdents($ip, $port, $user, $mdp);
                    
                    if ($ssh != false) {
                        // On génère un identifiant unique de 23 caractères
                        $priv_keyfile = uniqid('', true);
                        
                        // On génère la paire de clé et on upload la clé publique
                        $ssh->createKeyPair($priv_keyfile);

                        // On enregistre les données dans la bdd
                        $vm = new Vm();
                        $vm->ip = $ip;
                        $vm->port = $port;
                        $vm->user = $user;
                        $vm->keyfile = $priv_keyfile;
                        $vm->save();

                        // On termine par afficher l'action "show"
                        $this->app()->httpResponse()->redirect('vm/show');
                    }
                    else $erreurs[] = 'idents';
                }
                else $erreurs[] = 'exists';
            }
        }
        
        $this->page->addTpl('vm/add', array('erreurs' => $erreurs, 'form' => $form));
    }

    // Permet de modifier une VM
    protected function runEdit($args) {
        $vmId = $vars['id'];
        $form = Doctrine_Core::getTable('Vm')->find($vmId); $erreurs = array();

        if (!$form) $this->app()->httpResponse()->redirect('vm');
        
        // On traite le formulaire si celui-ci a été transmis
        if (Form::hasSend()) {
            list($erreurs, $form) = Form::verifyData(array(
                'port' => FIELD_PORT,
                'user' => FIELD_TEXT,
                'mdp' => FIELD_TEXT));

            if (!$erreurs) {
                extract($infos);
                $ssh = SSH::isValidIdents($vm->ip, $port, $user, $mdp);

                if ($ssh != false) {
                    // On génère un identifiant unique de 23 caractères
                    $priv_keyfile = uniqid('', true);

                    // On supprime l'ancienne paire de clés et on en régénère une nouvelle
                    $ssh->deleteKeyPair($vm->keyfile);
                    $ssh->createKeyPair($priv_keyfile);

                    // On enregistre les données dans la bdd
                    $vm->port = $port;
                    $vm->user = $user;
                    $vm->keyfile = $priv_keyfile;
                    $vm->save();

                    // On termine par afficher l'action "show"
                    $this->app()->httpResponse()->redirect('vm/show');
                }
                else $this->page->setTplVars('vm/add', array('idents' => true));
            }
        }

        $this->page->addTplVars('vm/edit', array('vm' => $vm));
    }

    // Cette méthode permet de supprimer une vm
    protected function runDel($args) {
        // On extrait les variables retourné par le routeur
        extract($args);

        if ($vm = Doctrine_Core::getTable('Vm')->find($id)) {
            // On supprime la clé privée
            $ssh = SSH::get($vm->ip, $vm->port, $vm->user, $vm->keyfile);
            $ssh->deleteKeyPair();

            // On supprime l'entrée dans la bdd
            $vm->delete();
        }

        $this->app()->httpResponse()->redirect('vm');
    }

    // Permet de texter le fonctionnement de la connexion ssh
    protected function runTestConnexion($vars) {
        // On extrait les variables retourné par le routeur
        extract($vars);

        // On vérifie que le vm existe et soit accessible par l'utilisateur
        // Si ce n'est pas le cas, on redirige l'utilisateur
        if ($vm = Doctrine_Core::getTable('Vm')->find($id)) {
            // On créer une connexion SSH avec la clé afin de vérifier la connexion
            $ssh = SSH::get($vm->ip, $vm->port, $vm->user, $vm->keyfile);
            $this->page->addTpl('vm/test', array('test' => $ssh->testConnexion()));
        }
        else $this->app()->httpResponse()->redirect('vm/show');
    }
}
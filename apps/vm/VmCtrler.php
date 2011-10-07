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
                // On vérifie que cette VM et ce user ne soit pas déjà pris en charge
                $table = Doctrine_Core::getTable('Vm');
                $exists = $table->exists($form['ip'], $form['port'], $form['user']);

                
                if ($exists == false) {
                    $add = $table->addVM($form['ip'], $form['port'], 
                        $form['user'], $form['mdp']);
                    
                    if ($add) {
                        $this->app()->httpResponse()->redirect('vm');
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
        $vmId = $args['id']; $erreurs = array();
        $table = Doctrine_Core::getTable('Vm');
        $vm = $table->getHydrateVM($vmId);
        
        // On vérifie que la VM sélectionné existe réellement
        if (!$vm) $this->app()->httpResponse()->redirect('vm');
        
        // On traite le formulaire si celui-ci a été transmis
        if (Form::hasSend()) {
            list($erreurs, $form) = Form::verifyData(array(
                'port' => FIELD_PORT,
                'user' => FIELD_TEXT,
                'mdp' => FIELD_TEXT));

            if (!$erreurs) {
                // On vérifie que la VM ne soit pas déjà pris en charge
                $exists = $table->exists($vm['ip'], $form['port'], $form['user']);
                
                if ($exists == false || $exists == $vmId) {
                    // On édite la VM. La fonction renvoie false si les idents sont faux
                    $edit = $table->editVM($vmId, $vm['ip'], $form['port'], $form['user'], 
                        $form['mdp'], $vm['keyfile']);
                    
                    if ($edit) {
                        $this->app()->httpResponse()->redirect('vm');
                    }
                    else $erreurs[] = 'idents';
                }
                else $erreurs[] = 'exists';
            }
        }

        $this->page->addTpl('vm/add', array('form' => $vm, 'erreurs' => $erreurs));
    }

    // Cette méthode permet de supprimer une vm
    protected function runDel($args) {
        // On extrait les variables retourné par le routeur
        $vmId = $args['id'];

        if ($vm = Doctrine_Core::getTable('Vm')->find($vmId)) {
            // On supprime la clé privée
            $ssh = SSH::get($vm->ip, $vm->port, $vm->user, $vm->keyfile);
            $ssh->deleteKeyPair();

            // On supprime l'entrée dans la bdd
            $vm->delete();
        }

        $this->app()->httpResponse()->redirect('vm');
    }

    // Permet de texter le fonctionnement de la connexion ssh
    protected function runTestConnexion($args) {
        $vmId = $args['id'];

        // On vérifie que le vm existe et soit accessible par l'utilisateur
        // Si ce n'est pas le cas, on redirige l'utilisateur
        if ($vm = Doctrine_Core::getTable('Vm')->getHydrateVM($vmId)) {
            // On créer une connexion SSH avec la clé afin de vérifier la connexion
            $ssh = SSH::get($vm['ip'], $vm['port'], $vm['user'], $vm['keyfile']);
            $this->page->addTpl('vm/test', array('test' => $ssh->testConnexion()));
        }
        else $this->app()->httpResponse()->redirect('vm/show');
    }
}
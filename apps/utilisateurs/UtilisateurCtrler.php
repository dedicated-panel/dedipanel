<?php
class UtilisateurCtrler extends BaseCtrler {
    protected function init() {
        parent::init();
        
        // On charge le fichier de traduction commun du module
        $this->lang->loadTradFile('utilisateurs/utilisateur');
    }
    
    // Permet à un utilisateur de se connecter
    protected function runLogin() {
        // On redirige l'utilisateur s'il est déjà connecté
        if ($this->session->connected) $this->app()->httpResponse()->redirect('utilisateur/menu');
        
        $erreurs = array();
        
        // On vérifie si le formulaire de connexion a été envoyé
        if (Form::hasSend()) {
            // Si c'est le cas, on récupère les infos pour les vérifier
            list($erreurs, $idents) = Form::verifyData(array(
                'pseudo' => FIELD_TEXT, 
                'mdp' => FIELD_MDP
            ));
            
            // On continue la vérification s'il n'y a pas encore d'erreurs
            if (!$erreurs) {
                // On vérifie la paire d'identifiants
                $table = Doctrine_Core::getTable('User');
                $user = $table->isValidIdents($idents['pseudo'], sha1($idents['mdp']));
                
                if ($user !== false) {
                    $this->session->connected = true;
                    $this->session->uid = $user['id'];
                    $this->session->pseudo = $user['pseudo'];
                    $this->session->email = $user['email'];
                    $this->session->lang = $user['lang'];
                    
                    $this->app()->httpResponse()->redirect('utilisateur/menu');
                }
                else $erreurs[] = 'idents';
            }
        }
        
        // On affiche le template en transmettant l'array 
        // Contenant les éventuelles erreurs rencontrés
        $this->page->addTpl('utilisateur/login', array('erreurs' => $erreurs));
    }
    
    // Permet à un utilisateur de se déconnecter. On vérifie d'abord qu'il soit connecté
    protected function runLogout() {
        if (!$this->session->connected) {
            $this->app()->httpResponse()->redirect('utilisateur/login');
        }
        
        // On détruit la session & on redirige l'utilisateur vers le formulaire de connexion
        $this->session->destroy();
        $this->app()->httpResponse()->redirect('utilisateur/login');
    }

    // Permet d'afficher le menu utilisateur
    protected function runMenu() {
        // Il est nécessaire d'être loggué pour accéder à cette page
        if (!$this->session->connected) {
            $this->app()->httpResponse()->redirect('utilisateur/login');
        }
        
        $this->page->addTpl('utilisateur/menu');
    }
    
    // Permet d'afficher/modifier le profil de l'utilisateur courrant
    protected function runProfil() {
        // Il faut être connecté pour accéder à cette page
        if (!$this->session->connected) {
            $this->app()->httpResponse()->redirect('utilisateur/login');
        }
        
        $erreurs = array(); $modif = false;
        $form = array('pseudo' => $this->session->pseudo, 'email' => $this->session->email);
        
        if (Form::hasSend()) {
            list($erreurs, $form) = Form::verifyData(array(
                'pseudo' => FIELD_TEXT, 
                'mdp' => FIELD_MDP, 
                'mdp2' => FIELD_MDP, 
                'email' => FIELD_EMAIL, 
                'lang' => FIELD_TEXT
            ));
            
            // Si aucun des deux mdp n'est défini c'est que
            // L'utilisateur ne souhaite pas modifier son mdp et donc il n'y a pas d'erreurs
            if (empty($form['mdp']) && empty($form['mdp2'])) {
                // On cherche la clé des deux erreurs pour les supprimés
                $mdpErr  = array_search('mdp', $erreurs);
                $mdp2Err = array_search('mdp2', $erreurs);
                
                unset($erreurs[$mdpErr], $erreurs[$mdp2Err]);
                $form['mdp'] = null;
            } 
            // On vérifie que les deux mots de passes correspondent
            elseif ($form['mdp'] != $form['mdp2']) {
                $erreurs[] = 'conf';
            }
            
            // On vérifie également que la langue soit correcte
            if (!$this->lang->isValidLang($form['lang'])) {
                $erreurs[] = 'lang';
            }
            
            // Ainsi que l'utilisation du pseudo/email
            $table = Doctrine_Core::getTable('User'); $uid = $this->session->uid;
            $idIdents = $table->existsIdents($form['pseudo'], $form['email']);
            if ($idIdents != $uid) {
                $erreurs[] = 'existsIdents';
            }
            
            if (!$erreurs) {
                $modif = $table->modifyUser($uid, $form['pseudo'], 
                    $form['email'], $form['lang'], $form['mdp']);
                
                $this->session->pseudo = $form['pseudo'];
                $this->session->email = $form['email'];
                $this->session->lang = $form['lang'];
                
                // On redirige l'utilisateur pour que l'interface soit traduite diretement
                $this->app()->httpResponse()->redirect('utilisateur/profil');
            }
        }
        
        $this->lang->loadTradFile('common/langs');
        $this->page->addTpl('utilisateur/profil', 
            array('erreurs' => $erreurs, 'form' => $form, 'modif' => $modif, 
              'langs' => $this->lang->getValidLangs(), 'lang' => $this->session->lang));
    }
}
?>
<?php
class UtilisateurCtrler extends BaseCtrler {
    protected function init() {
        parent::init();
        
        // On charge le fichier de traduction commun du module
        $this->lang->loadTraductionFile('apps/utilisateurs/langs/utilisateur');
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
                    
                    $this->app()->httpResponse()->redirect('utilisateur/menu');
                }
                else $erreurs['idents'] = true;
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
        
        $erreurs = array();
        
        $this->page->addTpl('utilisateur/profil', array('erreurs' => $erreurs));
    }
}
?>
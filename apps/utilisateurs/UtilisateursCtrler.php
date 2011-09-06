<?php
class UtilisateursCtrler extends BaseCtrler {
    // On réécrit la méthode init() afin de vérifier que l'utilisateur soit connecté
    protected function init() {
        parent::init();
        
        if (!$this->session->connected) {
            $this->app()->httpResponse()->redirect('utilisateur/login');
        }
        
        // On charge le fichier de traduction commun du module
        $this->lang->loadTradFile('utilisateurs/utilisateurs');
    }
    
    // Cette action permet de lister l'ensemble des utilisateurs enregistrés
    protected function runShow() {
        $users = Doctrine_Query::create()->select('id, pseudo, email')->from('User')
            ->execute();
        
        $this->page->addTpl('utilisateurs/show', array('users' => $users));
    }
    
    // Cette action permet d'ajouter un utilisateur
    protected function runAdd() {
        $form = array(); $erreurs = array();
        
        if (Form::hasSend()) {
            list($erreurs, $form) = Form::verifyData(array(
                'pseudo' => FIELD_TEXT, 
                'mdp' => FIELD_MDP, 
                'mdp2' => FIELD_MDP, 
                "email" => FIELD_EMAIL, 
                'lang' => FIELD_TEXT
            ));
            
            // On vérifie que les deux mots de passes correspondent
            if ($form['mdp'] != $form['mdp2']) {
                $erreurs[] = 'conf';
            }
            // On vérifie également que la langue soit correcte
            if (!$this->lang->isValidLang($form['lang'])) {
                $erreurs[] = 'lang';
            }
            
            if (!$erreurs) {
                // On créer un utilisateur, on ajoute les données du formulaire
                // Et on sauvegarde dans la bdd
                $user = new User();
                $user->pseudo = $form['pseudo'];
                $user->email = $form['email'];
                $user->mdp = $form['mdp'];
                $user->lang = $form['lang'];
                $user->save();
                
                var_dump($user);
            }
        }
        
        $this->page->addTpl('utilisateurs/add', 
            array('langs' => $this->lang->getValidLangs(), 'form' => $form, 
                'erreurs' => $erreurs));
    }
    
    // Cette action permet de supprimer un utilisateur
    protected function runDel($args) {
        var_dump($args);
    }
}
?>
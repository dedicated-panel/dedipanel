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
            
            // On récupère une instance de la table
            // Afin de faire des vérifs supplémentaires 
            // Sur l'utillisation des identss
            $table = Doctrine_Core::getTable('User');
            if ($table->existIdents($form['pseudo'], $form['email']) != false) {
                $erreurs[] = 'existIdents';
            }
            
            if (!$erreurs) {
                // On créer un utilisateur, on ajoute les données du formulaire
                // Et on sauvegarde dans la bdd
                $user = new User();
                $user->pseudo = $form['pseudo'];
                $user->email = $form['email'];
                $user->mdp = sha1($form['mdp']);
                $user->lang = $form['lang'];

                // Si Doctrine renvoie false, c'est que notre validateur email
                // Ne passe pas, le domaine indiqué est donc invalide
                // Soit on sauvegarde les données et on redirige l'utilisateur
                // Soit on ajoute un message d'erreur
                if ($user->isValid()) {
                    $user->save();
                    $this->app()->httpResponse()->redirect('utilisateurs');
                }
                else {
                    $erreurs[] = 'nddEmail';
                }
            }
        }
        
        $this->lang->loadTradFile('common/langs');
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
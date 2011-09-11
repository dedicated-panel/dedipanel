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
        $groups = Doctrine_Core::getTable('Group')->getGroups();
//        $form['groups'] = array();
        
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
                    $uid = $user->id;
                    
                    // On ajoute les groupes voulues
                    if (isset($_POST['groups'])) {
                        foreach ($_POST['groups'] AS $group) {
                            $access = new UserGroup();
                            $access->user_id = $uid;
                            $access->group_id = $group;
                            $access->save();
                        }
                    }
//                    $this->app()->httpResponse()->redirect('utilisateurs');
                }
                else {
                    $erreurs[] = 'nddEmail';
                }
            }
        }
        
        $this->lang->loadTradFile('common/langs');
        $this->page->addTpl('utilisateurs/add', 
            array('langs' => $this->lang->getValidLangs(), 'form' => $form, 
                'groups' => $groups, 'erreurs' => $erreurs, 'action' => 'add'));
    }
    
    // Cette action permet de modifier un utilisateur
    protected function runEdit($args) {
        $uid = $args['uid'];
        $table = Doctrine_Core::getTable('User');
        $erreurs = array(); $form = $table->getHydrateUser($uid);
        $groups = Doctrine_Core::getTable('Group')->getGroups();
        
        if (Form::hasSend()) {
            list($erreurs, $form) = Form::verifyData(array(
                'pseudo' => FIELD_TEXT, 
                'mdp' => FIELD_MDP, 
                'mdp2' => FIELD_MDP, 
                'email' => FIELD_EMAIL, 
                'lang' => FIELD_TEXT
            ));
            
            // On commence par vérifier si l'utilisateur souhaite modifier le mdp
            // Auquel cas on supprime les messages d'erreurs, et les valeurs dans l'array $form
            if (empty($form['mdp']) && empty($form['mdp2'])) {
                // On cherche la clé des deux erreurs pour les supprimés
                $mdpErr  = array_search('mdp', $erreurs);
                $mdp2Err = array_search('mdp2', $erreurs);
                
                unset($erreurs[$mdpErr], $erreurs[$mdp2Err]);
                $form['mdp'] = null;
            }
            // Sinon, on vérifie que les deux mots de passes correspondent
            elseif ($form['mdp'] != $form['mdp2']) {
                $erreurs[] = 'conf';
            }
            
            // On vérifie également que la langue soit correcte
            if (!$this->lang->isValidLang($form['lang'])) {
                $erreurs[] = 'lang';
            }
            
            // On vérifie que le pseudo et l'email
            // Ne sont pas utilisé par un autre utilisateur
            if ($table->existIdents($form['pseudo'], $form['email']) != $uid) {
                $erreurs[] = 'existIdents';
            }
            if (!$erreurs) {
                // On modifie l'utilisateur et on redirige
                $table->modifyUser($uid, $form['pseudo'], $form['email'], 
                    $form['lang'], $form['mdp'], $_POST['groups']);
                
                $this->app()->httpResponse()->redirect('utilisateurs');
            }
        }
        
        // On utilise le même template que pour l'ajout d'un utilisateur
        $this->lang->loadTradFile('common/langs');
        $this->page->addTpl('utilisateurs/add', 
            array('langs' => $this->lang->getValidLangs(), 'groups' => $groups, 
                'form' => $form, 'erreurs' => $erreurs, 'action' => 'edit'));
    }
    
    // Cette action permet de supprimer un utilisateur
    protected function runDel($args) {
        $uid = $args['uid'];
        
        // On supprime l'utilisateur voulu et on redirige l'admin
        Doctrine_Core::getTable('User')->delete($uid);
        $this->app()->httpResponse()->redirect('utilisateurs');
    }
}
?>
<?php
class GroupsCtrler extends BaseCtrler {
    protected function init() {
        parent::init();
        $this->lang->loadTradFile('utilisateurs/groups');
    }
    
    // Cette action permet de visualiser l'ensemble des groupes
    protected function runShow() {
        $groups = Doctrine_Core::getTable('Group')->getGroups();
        $this->page->addTpl('groups/show', array('groups' => $groups));
    }
    
    // Cette action permet d'ajouter un groupe
    protected function runAdd() {
        $form = array(); $erreurs = array();
        $vms = Doctrine_Core::getTable('Vm')->getVMs();
        
        if (Form::hasSend()) {
            list($erreurs, $form) = Form::verifyData(array(
                'nom' => FIELD_TEXT
            ));
            
            if (!$erreurs) {
                // On vérifie que le nom du groupe ne soit pas déjà utilisé
                if (Doctrine_Core::getTable('Group')->usedName($form['nom']) == false) {
                    // On sauvegarde le groupe et on récupère l'id
                    $group = new Group();
                    $group->nom = $form['nom'];
                    $group->save(); $gid = $group->id;

                    // On vérifies les cases à cochés
                    foreach ($vms AS $vm) {
                        $accessCase = $vm['id'] . '_1'; $access = false;
                        $adminCase = $vm['id'] . '_2'; $admin = false;

                        // On regarde si les cases sont cochés
                        if (isset($_POST[$accessCase])) {
                            $access = true;
                            if (isset($_POST[$adminCase])) $admin = true;
                        }

                        // On créer une ligne dans la bdd (si nécessaire)
                        if ($access) {
                            $droits = new GroupVm();
                            $droits->group_id = $gid;
                            $droits->vm_id = $vm['id'];
                            $droits->access = $access;
                            $droits->admin = $admin;
                            $droits->save();
                        }
                    }

                    $this->app()->httpResponse()->redirect('groups');
                }
                else $erreurs[] = 'usedName';
            }
        }
        
        $this->page->addTpl('groups/add', 
            array('action' => 'Add', 'form' => $form, 'erreurs' => $erreurs, 'VMs' => $vms));
    }
    
    // Permet de modifier un groupe
    protected function runEdit($args) {
        // On récupère l'id du groupe
        $gid = $args['gid'];
        
        // On initialise les variables du template
        $erreurs = array();
        $form = Doctrine_Core::getTable('Group')->getGroup($gid);
        $vms = Doctrine_Core::getTable('Vm')->getVMs();
        
        if (Form::hasSend()) {
            list($erreurs, $form) = Form::verifyData(array(
                'nom' => FIELD_TEXT
            ));
            
            if (!$erreurs) {
                // On vérifie que le nom du groupe ne soit pas déjà utilisé
                // Ou que l'id corresponde à celui du group modifié
                $nameUsed = Doctrine_Core::getTable('Group')->usedName($form['nom']);
                if ($nameUsed == $gid || $nameUsed == false) {
                    // On sauvegarde le nom
                    $group = Doctrine_Core::getTable('Group')->find($gid);
                    $group->nom = $form['nom'];
                    $group->save();
                    
                    // On supprimes les droits sauvegardés pour les recréer en dessous
                    $group->GroupVms->delete();

                    // On vérifies les cases à cochés
                    foreach ($vms AS $vm) {
                        $accessCase = $vm['id'] . '_1'; $access = false;
                        $adminCase = $vm['id'] . '_2'; $admin = false;

                        // On regarde si les cases sont cochés
                        if (isset($_POST[$accessCase])) {
                            $access = true;
                            if (isset($_POST[$adminCase])) $admin = true;
                        }

                        // On créer une ligne dans la bdd (si nécessaire)
                        if ($access) {
                            $droits = new GroupVm();
                            $droits->group_id = $gid;
                            $droits->vm_id = $vm['id'];
                            $droits->access = $access;
                            $droits->admin = $admin;
                            $droits->save();
                        }
                    }

                    $this->app()->httpResponse()->redirect('groups');
                }
                else $erreurs[] = 'usedName';
            }
        }
        
        $this->page->addTpl('groups/add', 
            array('action' => 'Edit', 'form' => $form, 'erreurs' => $erreurs, 'VMs' => $vms));
    }
    
    // Cette action permet de supprimer un groupe
    protected function runDel($args) {
        $gid = intval($args['gid']);
        
        Doctrine_Core::getTable('Group')->delete($gid);
        $this->app()->httpResponse()->redirect('groups');
    }
}
?>
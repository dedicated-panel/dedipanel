<?php
class GroupsCtrler extends BaseCtrler {
    protected function init() {
        parent::init();
        $this->lang->loadTradFile('utilisateurs/groups');
    }
    
    // Cette action permet de visualiser l'ensemble des groupes
    protected function runShow() {
        $groups = array();
        $this->page->addTpl('groups/show', array('groups' => $groups));
    }
    
    // Cette action permet d'ajouter un groupe
    protected function runAdd() {
        $form = array(); $erreurs = array();
        
        $this->page->addTpl('groups/add', 
            array('form' => $form, 'erreurs' => $erreurs));
    }
}
?>
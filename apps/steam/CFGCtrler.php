<?php
class CFGCtrler extends BaseCtrler {
    // On réécrit la fonction lancant les méthodes correspondant à l'action
    // Afin de s'assurer que ce controller n'est appelé qu'avec les droits nécessaires
    public function run($action, $vars) {
        if (!$this->session->connected) {
            $this->app()->httpResponse()->redirect('utilisateur/login');
        }

        // On vérifie que le serveur appartient à l'utilisateur
        $uid = $this->session->uid;
        $serv = Doctrine_Core::getTable('Steam')->findByIdAndUid($vars['id'], $uid);
        $serv = $serv->getFirst();

        if (!$serv) $this->app()->httpResponse()->redirect('steam/show');
        $vars['serv'] = $serv;

        return parent::run($action, $vars);
    }

    protected function runAdd($vars) {
        $serv = $vars['serv']; $vm = $serv->Vm;
        $gameDir = $serv->getGameDir(); $dir = $gameDir;

        // On récupère le répertoire indiqué s'il y en a un
        if (isset($vars['dir'])) {
            $dir .= $vars['dir'] . '/';
        }

        $this->page->addTpl('steam/cfg/add', array('filename' => '', 'content' => ''));

        if (Form::hasSend()) {
            list($erreurs, $form) = Form::verifyData(array(
                'filename' => 'string',
                'content' => 'string'));
            
            if (!$erreurs) {
                $filePath = $dir . $form['filename']; var_dump($filePath);
                $ssh = SSH::get($vm->ip, $vm->port, $vm->user, $vm->keyfile);
                $ssh->putData($form['content'], $filePath);

//                $this->app()->httpResponse()->redirect('steam/show');
            }
        }
    }

    // Cette méthode permet de modifier un fichier
    protected function runEdit($vars) {
        $serv = $vars['serv']; $vm = $serv->Vm; $gameDir = $serv->getGameDir();
        $filename = $vars['file']; $filePath = $gameDir . $filename;

        // On récupère une connexion ssh afin de recevoir le contenu du fichier
        $ssh = SSH::get($vm->ip, $vm->port, $vm->user, $vm->keyfile);
        $file = $ssh->getFile($filePath);

        $this->page->addTpl('steam/cfg/edit', array('sid' => $vars['id'], 'content' => $file, 
            'filename' => $filename, 'filePath' => substr($filePath, strlen($gameDir))));
    }

    // Cette méthode permet de supprimer un fichier cfg
    protected function runDel($vars) {
        $serv = $vars['serv']; $vm = $serv->Vm;
        $filePath = $serv->getGameDir() . $vars['file'];

        $ssh = SSH::get($vm->ip, $vm->port, $vm->user, $vm->keyfile);
        $ssh->exec('rm ' . $filePath);

        $this->app()->httpResponse()->redirect(
            'steam/cfg/' . $vars['id'] . '/' . $filename . '/..');
    }

    // Cette méthode permet d'afficher la racine du serveur
    protected function runShow($vars) {
        $serv = $vars['serv']; $vm = $serv->Vm;
        $gameDir = $serv->getGameDir();
        $dir = $gameDir;

        // On récupère le répertoire indiqué s'il y en a un
        if (isset($vars['dir'])) {
            $dir = $gameDir . $vars['dir'] . '/';
            var_dump($dir);
        }

        // On créer une connexion ssh à partir de laquelle
        // On va récupérer la liste des fichiers & dossiers présents dans ledit dossier
        $ssh = SSH::get($vm->ip, $vm->port, $vm->user, $vm->keyfile);
        $dirList = $ssh->getDirList($dir);

        $this->page->addTpl('steam/cfg/show', array(
            'sid' => $vars['id'], 'dirList' => $dirList,
            'selectedDir' => $dir, 'gameDir' => $gameDir));
    }
}
?>
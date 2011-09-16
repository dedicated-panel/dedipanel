<?php
define('ROOT_DIR', dirname(__FILE__));
define('LIBS_DIR', ROOT_DIR . '/libs/');
define('MDLS_DIR', LIBS_DIR . 'models/');
define('CFG_DIR', ROOT_DIR . '/configs/');

include_once LIBS_DIR . 'Core/Utils.class.php';
include_once LIBS_DIR . 'Doctrine/Core.php';

// On utilise les deux autoloaders de Doctrine.
// Le premier sert à charger Doctrine lui même, le second sert a charger nos modèles
spl_autoload_register(array('Doctrine_Core', 'autoload'));
spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));

$config = JSON::loadCfg(CFG_DIR . 'config.json');
$config = $config['db'];

$mgr = Doctrine_Manager::getInstance();
$mgr->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, $config['prefixe'] . '_%s');

$conn = Doctrine_Manager::connection($config['dsn']);

// On supprime la bdd actuelle et on la recréer
echo 'Suppression de la base de données.<br />';
Doctrine_Core::dropDatabases();
Doctrine_Core::createDatabases();

echo 'Génération des modèles doctrine.<br />';
$dbYaml = CFG_DIR . '/db.yml';

$genOptions = array(
    'generateTableClasses' => true,
    'baseClassesDirectory' => '.');
Doctrine_Core::generateModelsFromYaml($dbYaml, MDLS_DIR, $genOptions);

echo 'Création des tables de la base de données.<br />';
Doctrine_Core::createTablesFromModels(MDLS_DIR);

$conn->execute('
    INSERT INTO dp_users (pseudo, mdp, email, lang, su) VALUES 
    ("nir", SHA1("test"), "stratege3@gmail.com", "fr", 1), 
    ("test", SHA1("test"), "test@free.fr", "fr", 0);');
echo 'Insertion de 2 utilisateurs de test.<br />';

$conn->execute('
    INSERT INTO dp_vm (ip, port, user, keyfile) VALUES 
    ("87.98.165.155", "9787", "dp-css", "4e6806032c0488.07560869"), 
    ("87.98.165.155", "9787", "dp-cs", "4e6d11ed147e19.61514669"), 
    ("87.98.165.155", "9787", "dp-cz", "4e6d20a9558b25.80527680"), 
    ("87.98.165.155", "9787", "voip", "4e6d21db0a0735.67553080");');
echo 'Insertion de 4 VMs de test.<br />';
?>
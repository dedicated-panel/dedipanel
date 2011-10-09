<?php
define('ROOT_DIR', dirname(__FILE__));
define('LIBS_DIR', ROOT_DIR . '/libs/');
define('MDLS_DIR', LIBS_DIR . 'models/');
define('CFG_DIR', ROOT_DIR . '/configs/');
define('SOCK_DIR', LIBS_DIR . 'Socket/');
define('QUERY_DIR', LIBS_DIR . 'Steam/');

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

$conn->execute("
    INSERT INTO dp_jeux (nomjeu, installname, launchname, bin, source, orangebox, map) VALUES
    ('Counter Strike 1.6', 'cstrike', 'cstrike', 'hlds_run', 0, 0, 'de_dust2'),
    ('Counter Strike : Condition Zero', 'czero', 'czero', 'hlds_run', 0, 0, 'de_dust2_cz'),
    ('Deathmatch Classic', 'dmc', 'dmc', 'hlds_run', 0, 0, 'dmc_dm2'),
    ('Day of Defeat', 'dod', 'dod', 'hlds_run', 0, 0, 'dod_anzio'),
    ('Gearbox', 'gearbox', 'gearbox', 'hlds_run', 0, 0, 'of0a0'),
    ('Ricochet', 'ricochet', 'ricochet', 'hlds_run', 0, 0, 'Rc_deathmatch');");
echo 'Insertion de 6 Jeux de test.<br />';
?>
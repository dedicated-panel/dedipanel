<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141128024805 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE fos_user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_583D1F3E5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_C560D76192FC23A8 (username_canonical), UNIQUE INDEX UNIQ_C560D761A0D96FBF (email_canonical), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user_user_group (user_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_B3C77447A76ED395 (user_id), INDEX IDX_B3C77447FE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plugin (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, downloadUrl VARCHAR(128) NOT NULL, scriptName VARCHAR(32) NOT NULL, packetDependencies LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', version VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, installName VARCHAR(24) NOT NULL, steamCmd TINYINT(1) NOT NULL, launchName VARCHAR(24) NOT NULL, bin VARCHAR(24) NOT NULL, appId INT DEFAULT NULL, appMod VARCHAR(20) DEFAULT NULL, orangebox TINYINT(1) NOT NULL, source TINYINT(1) NOT NULL, map VARCHAR(20) DEFAULT NULL, available TINYINT(1) NOT NULL, binDir VARCHAR(20) DEFAULT NULL, sourceImagesMaps VARCHAR(255) DEFAULT NULL, type VARCHAR(32) NOT NULL, configTemplate LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_plugin (game_id INT NOT NULL, plugin_id INT NOT NULL, INDEX IDX_945B8A5CE48FD905 (game_id), INDEX IDX_945B8A5CEC942BCF (plugin_id), PRIMARY KEY(game_id, plugin_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE machine (id INT AUTO_INCREMENT NOT NULL, privateIp VARCHAR(15) DEFAULT NULL, publicIp VARCHAR(15) DEFAULT NULL, port INT NOT NULL, user VARCHAR(16) NOT NULL, privateKey VARCHAR(23) NOT NULL, publicKey VARCHAR(255) DEFAULT NULL, home VARCHAR(255) DEFAULT NULL, nbCore INT DEFAULT NULL, is64bit TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gameserver (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, port INT NOT NULL, installationStatus INT DEFAULT NULL, dir VARCHAR(64) NOT NULL, maxplayers INT NOT NULL, rconPassword VARCHAR(32) NOT NULL, machineId INT DEFAULT NULL, gameId INT DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_E26640E5633EC4FD (machineId), INDEX IDX_E26640E5EC55B7A4 (gameId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gameserver_plugins (server_id INT NOT NULL, plugin_id INT NOT NULL, INDEX IDX_9B4F31881844E6B7 (server_id), INDEX IDX_9B4F3188EC942BCF (plugin_id), PRIMARY KEY(server_id, plugin_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE steam_server (id INT NOT NULL, rebootAt TIME DEFAULT NULL, munin TINYINT(1) DEFAULT NULL, sv_passwd VARCHAR(16) DEFAULT NULL, core INT DEFAULT NULL, hltvPort INT DEFAULT NULL, mode VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE minecraft_server (id INT NOT NULL, queryPort INT DEFAULT NULL, rconPort INT NOT NULL, minHeap INT NOT NULL, maxHeap INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_classes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_type VARCHAR(200) NOT NULL, UNIQUE INDEX UNIQ_69DD750638A36066 (class_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_security_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, identifier VARCHAR(200) NOT NULL, username TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8835EE78772E836AF85E0677 (identifier, username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_object_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, parent_object_identity_id INT UNSIGNED DEFAULT NULL, class_id INT UNSIGNED NOT NULL, object_identifier VARCHAR(100) NOT NULL, entries_inheriting TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_9407E5494B12AD6EA000B10 (object_identifier, class_id), INDEX IDX_9407E54977FA751A (parent_object_identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_object_identity_ancestors (object_identity_id INT UNSIGNED NOT NULL, ancestor_id INT UNSIGNED NOT NULL, INDEX IDX_825DE2993D9AB4A6 (object_identity_id), INDEX IDX_825DE299C671CEA1 (ancestor_id), PRIMARY KEY(object_identity_id, ancestor_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_entries (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_id INT UNSIGNED NOT NULL, object_identity_id INT UNSIGNED DEFAULT NULL, security_identity_id INT UNSIGNED NOT NULL, field_name VARCHAR(50) DEFAULT NULL, ace_order SMALLINT UNSIGNED NOT NULL, mask INT NOT NULL, granting TINYINT(1) NOT NULL, granting_strategy VARCHAR(30) NOT NULL, audit_success TINYINT(1) NOT NULL, audit_failure TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_46C8B806EA000B103D9AB4A64DEF17BCE4289BF4 (class_id, object_identity_id, field_name, ace_order), INDEX IDX_46C8B806EA000B103D9AB4A6DF9183C9 (class_id, object_identity_id, security_identity_id), INDEX IDX_46C8B806EA000B10 (class_id), INDEX IDX_46C8B8063D9AB4A6 (object_identity_id), INDEX IDX_46C8B806DF9183C9 (security_identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447FE54D947 FOREIGN KEY (group_id) REFERENCES fos_user_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_plugin ADD CONSTRAINT FK_945B8A5CE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE game_plugin ADD CONSTRAINT FK_945B8A5CEC942BCF FOREIGN KEY (plugin_id) REFERENCES plugin (id)');
        $this->addSql('ALTER TABLE gameserver ADD CONSTRAINT FK_E26640E5633EC4FD FOREIGN KEY (machineId) REFERENCES machine (id)');
        $this->addSql('ALTER TABLE gameserver ADD CONSTRAINT FK_E26640E5EC55B7A4 FOREIGN KEY (gameId) REFERENCES game (id)');
        $this->addSql('ALTER TABLE gameserver_plugins ADD CONSTRAINT FK_9B4F31881844E6B7 FOREIGN KEY (server_id) REFERENCES gameserver (id)');
        $this->addSql('ALTER TABLE gameserver_plugins ADD CONSTRAINT FK_9B4F3188EC942BCF FOREIGN KEY (plugin_id) REFERENCES plugin (id)');
        $this->addSql('ALTER TABLE steam_server ADD CONSTRAINT FK_6FF0B6A8BF396750 FOREIGN KEY (id) REFERENCES gameserver (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE minecraft_server ADD CONSTRAINT FK_4290D528BF396750 FOREIGN KEY (id) REFERENCES gameserver (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_object_identities ADD CONSTRAINT FK_9407E54977FA751A FOREIGN KEY (parent_object_identity_id) REFERENCES acl_object_identities (id)');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors ADD CONSTRAINT FK_825DE2993D9AB4A6 FOREIGN KEY (object_identity_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors ADD CONSTRAINT FK_825DE299C671CEA1 FOREIGN KEY (ancestor_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B806EA000B10 FOREIGN KEY (class_id) REFERENCES acl_classes (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B8063D9AB4A6 FOREIGN KEY (object_identity_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B806DF9183C9 FOREIGN KEY (security_identity_id) REFERENCES acl_security_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');

        $this->insertDefaultData();
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE fos_user_user_group DROP FOREIGN KEY FK_B3C77447FE54D947');
        $this->addSql('ALTER TABLE fos_user_user_group DROP FOREIGN KEY FK_B3C77447A76ED395');
        $this->addSql('ALTER TABLE game_plugin DROP FOREIGN KEY FK_945B8A5CEC942BCF');
        $this->addSql('ALTER TABLE gameserver_plugins DROP FOREIGN KEY FK_9B4F3188EC942BCF');
        $this->addSql('ALTER TABLE game_plugin DROP FOREIGN KEY FK_945B8A5CE48FD905');
        $this->addSql('ALTER TABLE gameserver DROP FOREIGN KEY FK_E26640E5EC55B7A4');
        $this->addSql('ALTER TABLE gameserver DROP FOREIGN KEY FK_E26640E5633EC4FD');
        $this->addSql('ALTER TABLE gameserver_plugins DROP FOREIGN KEY FK_9B4F31881844E6B7');
        $this->addSql('ALTER TABLE steam_server DROP FOREIGN KEY FK_6FF0B6A8BF396750');
        $this->addSql('ALTER TABLE minecraft_server DROP FOREIGN KEY FK_4290D528BF396750');
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B806EA000B10');
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B806DF9183C9');
        $this->addSql('ALTER TABLE acl_object_identities DROP FOREIGN KEY FK_9407E54977FA751A');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors DROP FOREIGN KEY FK_825DE2993D9AB4A6');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors DROP FOREIGN KEY FK_825DE299C671CEA1');
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B8063D9AB4A6');
        $this->addSql('DROP TABLE fos_user_group');
        $this->addSql('DROP TABLE fos_user_user');
        $this->addSql('DROP TABLE fos_user_user_group');
        $this->addSql('DROP TABLE plugin');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE game_plugin');
        $this->addSql('DROP TABLE machine');
        $this->addSql('DROP TABLE gameserver');
        $this->addSql('DROP TABLE gameserver_plugins');
        $this->addSql('DROP TABLE steam_server');
        $this->addSql('DROP TABLE minecraft_server');
        $this->addSql('DROP TABLE acl_classes');
        $this->addSql('DROP TABLE acl_security_identities');
        $this->addSql('DROP TABLE acl_object_identities');
        $this->addSql('DROP TABLE acl_object_identity_ancestors');
        $this->addSql('DROP TABLE acl_entries');
    }

    public function insertDefaultData()
    {
        $sql = <<<EOF
LOCK TABLES `game` WRITE;
INSERT INTO `game` VALUES
  (1,'Counter Strike','cstrike',1,'cstrike','hlds_run',90,'cstrike',0,0,'de_dust2',1,'','http://image.www.gametracker.com/images/maps/160x120/cs/','steam','// Use this file to configure your DEDICATED server. \n// This config file is executed on server start.\n\n// disable autoaim\nsv_aim 0\n\n// disable clients\' ability to pause the server\npausable 0\n\n// default server name. Change to \"Bob\'s Server\", etc.\nhostname \"{{ hostname }}\"\nsv_password \"{{ svPassword }}\"\nrcon_password \"{{ rconPassword }}\"\n\n// maximum client movement speed \nsv_maxspeed 320\n\n// 20 minute timelimit\nmp_timelimit 20\n\nsv_cheats 0\n\n// load ban files\nexec listip.cfg\nexec banned.cfg\n\n'),
  (2,'Counter-Strike: Condition Zéro','czero',1,'czero','hlds_run',90,'czero',0,0,'de_dust2',1,NULL,'http://image.www.gametracker.com/images/maps/160x120/czero/','steam','// Use this file to configure your DEDICATED server. \n// This config file is executed on server start.\n\n// disable autoaim\nsv_aim 0\n\n// disable clients\' ability to pause the server\npausable 0\n\n// default server name. Change to \"Bob\'s Server\", etc.\nhostname \"{{ hostname }}\"\nsv_password \"{{ svPassword }}\"\nrcon_password \"{{ rconPassword }}\"\n\n// maximum client movement speed \nsv_maxspeed 320\n\n// 20 minute timelimit\nmp_timelimit 20\n\nsv_cheats 0\n\n// load ban files\nexec listip.cfg\nexec banned.cfg\n'),
  (3,'Day of Defeat','dod',1,'dod','hlds_run',90,'dod',0,0,'dod_anzio',1,'','http://image.www.gametracker.com/images/maps/160x120/dod/','steam','//-----------------------------------------------\n// Server Config For Day Of Defeat v1.2 Server\n//-----------------------------------------------\n//\n// Special Thx to  [AR]-All Ready Online Network\n// for help with this server config\n\n\nhostname \"{{ hostname }}\"\nsv_password \"{{ svPassword }}\"\n//sv_spectator_password \"yourpasshere\" \nrcon_password  \"{{ rconPassword }}\"\n\n//-----------------------------------------------\n//           	Server Variables\n//-----------------------------------------------\nmp_autocrosshair 0\n//mp_flashlight 1\n//mp_teamplay 11 \nmp_friendlyfire 0\nmp_fraglimit 0\nmp_timelimit 30\n\nsv_allowdownload 1\nsv_allowupload 1\nsv_cheats 0\nsv_maxspectators 4\nsv_maxrate 6000  \nsv_minrate 0  \n\ndecalfrequency 30\nfakelag	0	\nfakeloss 0\npausable 0\n\n//-----------------------------------------------\n//   		Physics settings\n//-----------------------------------------------\nsv_accelerate 10\nsv_aim 0\nsv_airaccelerate 10\nsv_airmove 1\nsv_friction 4\nsv_gravity 800\nsv_bounce 1\nsv_clienttrace 3.5\nsv_clipmode 0\nsv_stepsize 18\nsv_stopspeed 100\nsv_maxspeed 500 \nsv_wateraccelerate 10\nsv_waterfriction 1\nedgefriction 2\nmp_falldamage 1    \nmp_footsteps 1\n\n//-----------------------------------------------\n//   		DoD Extra settings\n//-----------------------------------------------\n//This Enables DoD\'s Netcoding\nsv_unlag 1\n\n//DoD Beta 2.0 Bullet Tracer settings\ntraceroffset 100\ntracerlength 0.45\ntracerred 1.3\ntracerblue 0.1\ntracergreen 0.7\ntraceralpha 0.45\ntracerspeed 6250\n\n//Turn on\\off Spectating Team\nmp_allowspectators 1\n\n//Turn on/off death messages\nmp_deathmsg 1\n\n// load ban files\nexec listip.cfg\nexec banned.cfg\n\nmp_alliesclasses -1\nmp_axisclasses -1\nmp_spawnbazookas 1\n'),
  (4,'Team Fortress Classic','tfc',1,'tfc','hlds_run',90,'tfc',0,0,'2fort',1,'','http://image.www.gametracker.com/images/maps/160x120/tfc/','steam','// Use this file to configure your DEDICATED server. \n// This config file is executed on server startup.\n\n// disable autoaim\nsv_aim 0\n\n// player bounding boxes (collisions, not clipping)\nsv_clienttrace 3.5\n\n// disable clients\' ability to pause the server\npausable 0\n\n// default server name. Change to \"Bob\'s Server\", etc.\nhostname \"{{ hostname }}\"\n\n// maximum client movement speed \nsv_maxspeed 500\n\n// Set up teamplay variables\nmp_teamplay 21\n\n// 30 minute timelimit\nmp_timelimit 30\n\n// footsteps on\nmp_footsteps 1\n\n// Turn off autoteam\ntfc_autoteam 0\n\n// Turn on a prematch\n//tfc_clanbattle 1\n//tfc_clanbattle_prematch 2\n\nsv_password \"{{ svPassword }}\"\nrcon_password \"{{ rconPassword }}\"\n\n// load ban files\nexec listip.cfg\nexec banned.cfg\n'),
  (5,'Counter-Strike: Source','Counter-Strike Source',1,'cstrike','srcds_run',232330,NULL,0,1,'de_dust2',1,'','http://image.www.gametracker.com/images/maps/160x120/css/','steam','// CS:S Server Config file\n// mostly the default settings with rate limits set to prevent massive lag\n// qUiCkSiLvEr\n\necho =========================\necho executing CS:S Server.cfg\necho =========================\n\nhostname \"{{ hostname }}\"\n\n// set to force players to respawn after death\nmp_forcerespawn 1\n\n// enable player footstep sounds\nmp_footsteps 1\nsv_footsteps 1 \n\n//  Bounce multiplier for when physically simulated objects collide with other objects.\nsv_bounce 0\n\n// enable flashlight\nmp_flashlight 1\n\n// enable autocrosshair (default is 1)\nmp_autocrosshair 1\n\n// allow bots\nmp_allowNPCs 1\n\n// world gravity (default 800)\nsv_gravity 800\n\n// world friction (default 4)\nsv_friction 4\n\n// world water friction (default 1)\nsv_waterfriction 1\n\n// Minimum stopping speed when on ground\nsv_stopspeed 75\n\n// spectator settings\nsv_noclipaccelerate 5\nsv_noclipspeed 5\nsv_specaccelerate 5\nsv_specspeed 3\nsv_specnoclip 1\n\n// Misc settings (leave as is)\nmp_teamplay 0 \nmp_fraglimit 0 \nmp_falldamage 0 \nmp_weaponstay 0 \n\n// Allow friendlyfire to hurt teammates (default 0)\nmp_friendlyfire 0 \n\n// player movement acceleration rates (default 5, 10 and 10)\nsv_accelerate 5 \nsv_airaccelerate 10 \nsv_wateraccelerate 10 \n\n// max player speed (default 320)\nsv_maxspeed 320\n\n// misc physics settings, leave them as-is\nsv_rollspeed 200 \nsv_rollangle 0 \n\n// player steps and stepsize - dont mess with this (default 18)\nsv_stepsize 18 \n\n// misc default vehicle settings (leave these alone)\n//r_VehicleViewDampen 1 \n//r_JeepViewDampenFreq 7.0 \n//r_JeepViewDampenDamp 1.0 \n//r_JeepViewZHeight 10.0 \n//r_AirboatViewDampenFreq 7.0 \n//r_AirboatViewDampenDamp 1.0 \n//r_AirboatViewZHeight 0.0 \n\n// teamplay talk all (1) or team only (0)\nsv_alltalk 0\n\n// enable voice on server\nsv_voiceenable 1\n\n// disable pause on server (set this to 0 always)\nsv_pausable 0\n\n// disable cheats (set this to 0 always)\nsv_cheats 0\n\n// teamserver coop (default 0)\ncoop 0\n\n// deathmatch - set this to 1\ndeathmatch 1\n\n// allow players to upload sprays (default 1)\nsv_allowupload 1\n\n// allow sprays and map content to be downloaded (default 1)\nsv_allowdownload 1\n\n// how often players can spray tags (default 20)\ndecalfrequency 30\n\n// fast http download url address\n//sv_downloadurl\n\n// Enable instanced baselines - Saves network overhead\nsv_instancebaselines 1\n\n//Force server side preloading (default 0)\nsv_forcepreload 1\n\n// set timelimit before map change\n//mp_timelimit 30\n\n// How many minutes each round takes (min. 1.000000 max. 9.000000)\nmp_roundtime 5\n\n// Max bandwidth rate allowed on server, 0 == unlimited\nsv_maxrate 9999\n\n// Min bandwidth rate allowed on server, 0 == unlimited\nsv_minrate 5000\n\n// Maximum updates per second that the server will allow (default 60)\nsv_maxupdaterate 30\n\n// Minimum updates per second that the server will allow (default 10)\nsv_minupdaterate 10\n\n// Maximum lag compensation in seconds (min. 0.000000 max. 1.000000)\nsv_maxunlag 1\n\n// Maximum speed any ballistically moving object is allowed to attain per axis (default 3500)\nsv_maxvelocity 3000\n\n// rcon failure settings\n//Number of minutes to ban users who fail rcon authentication\nsv_rcon_banpenalty 0\n\n//Number of minutes to ban users who fail rcon authentication\nsv_rcon_maxfailures 10\n\n// Number of times a user can fail rcon authentication in sv_rcon_minfailuretime before being banned\nsv_rcon_minfailures 5\n\n// Number of seconds to track failed rcon authentications\nsv_rcon_minfailuretime 30\n\n//The region of the world to report this server in (255 = world)\nsv_region 255\n\n// gather server statistics\nsv_stats 1\n\n//After this many seconds without a message from a client, the client is dropped (default 65)\nsv_timeout 65\n\n//Enables player lag compensation\nsv_unlag 1\n\n//Specifies which voice codec DLL to use in a game. Set to the name of the DLL without the extension\nsv_voicecodec vaudio_miles\n\n//Enables HLTV on this server\nsv_hltv 0\n\n//toggles whether the server allows spectator mode or not\nmp_allowspectators 1\n\n// If set to 1 then Server is a lan server ( no heartbeat, no authentication, no non-class C addresses, 9999.0 rate, etc. )\n// set to 0 for internet server\nsv_lan 0\n\n// Server contact name / url / email\nsv_contact \"\"\n\n// server password for players to join (default \"\")\nsv_password \"{{ svPassword  }}\"\n\nrcon_password \"{{ rconPassword }}\" \n\n// execute SteamID based ban list\nexec banned_user.cfg\n\n// execute IP based ban list\nexec banned_ip.cfg\n\n// Set packet filtering by IP mode (default 1)\nsv_filterban 1\n\n// log all bans in logfile\nsv_logbans 1\n\n// here are a few useful alias\n//exec alias.cfg\n//exec rate.cfg\n\n// enable logging and start log file\nsv_logfile 1\nlog on\n'),
  (6,'Team Fortress 2','tf',1,'tf','srcds_run',232250,NULL,0,1,'ctf_2fort',1,'','http://image.www.gametracker.com/images/maps/160x120/tf2/','steam','// Paramètres générales //\n\n// Nom d\'hôte du serveur.\nhostname \"{{ hostname }}\"\n\n// Remplace les joueurs max rapportés par des clients potentiels\nsv_visiblemaxplayers 24\n\n// Le nombre maximum de tours à jouer avant que le serveur modifications les cartes\nmp_maxrounds 5\n\n// Défini pour verrouiller le temps par-image qui s\'écoule\nhost_framerate 0\n\n// Mettez l\'état de pause du serveur\nsetpause 0\n\n// Contrôle d\'où le client obtient le contenu \n// 0 = n\'importe où, 1 = partout inscrits sur la white list, 2 = contenu Steam officielle seulement\nsv_pure 0\n\n// Le serveur pausable\nsv_pausable 0\n\n// Type de serveur 0=internet 1=lan\nsv_lan 0\n\n// Si le serveur applique la cohérence des fichiers pour les fichiers critiques\nsv_consistency 1\n\n// Collecte les statistiques d\'utilisation du CPU\nsv_stats 1\n\n\n\n// Exécuter les utilisateur Bannis //\nexec banned_user.cfg\nexec banned_ip.cfg\nwriteid\nwriteip\n\n\n\n// Contact & Région //\n\n// Contact email pour le sysop serveur\nsv_contact emailaddy@google.com\n\n// La région du monde pour signaler ce serveur dedans.\n// -1 est le monde, 0 est la cote est des USA, 1 est la cote ouest des USA\n// 2 Amérique du sud, 3 Europe, 4 Asie, 5 Australie, 6 Moyen Orient, 7 Afrique\nsv_region 0\n\n\nsv_password \"{{ svPassword }}\"\n\n\n// Paramètres Rcon //\n\n// Mot de passe pour l\'authentification rcon\nrcon_password \"{{ rconPassword }}\"\n\n// Nombre de minutes pour bannir les utilisateur qui échoue l\'authentification rcon\nsv_rcon_banpenalty 1440\n\n// Le nombre maximum de fois qu\'un utilisateur peut échouer l\'authentification rcon avant d’être banni\nsv_rcon_maxfailures 5\n\n\n\n// Paramètres du journal //\n\n// Active la journalisation sur ficher, console, et udp < on | off >.\nlog on\n\n// Enregistrer les informations du serveur à un seul fichier.\nsv_log_onefile 0\n\n// Enregistrer les informations du serveur dans le fichier journal.\nsv_logfile 1\n\n// Journalisation des bans serveur bans dans le journal serveur.\nsv_logbans 1\n\n// Echo les informations du journal de la console.\nsv_logecho 1\n\n\n\n// Réglage de la vitesse //\n\n// Limiteur de vitesse fps\nfps_max 600\n\n// Bande passante minimum autorisé pour le serveur, 0 == illimitée\nsv_minrate 0\n\n// Bande passante maximale autorisée pour le serveur, 0 == illimitée\nsv_maxrate 20000\n\n// Mises à jour minimum par seconde que le serveur va permettre\nsv_minupdaterate 10\n\n// Mises à jour maximum par seconde que le serveur va permettre\nsv_maxupdaterate 66\n\n\n\n// Paramètres de téléchargement //\n\n// Permettre aux clients de télécharger des fichiers de personnalisés\nsv_allowupload 1\n\n// Permettre aux clients de télécharger des fichiers\nsv_allowdownload 1\n\n// Taille de fichier maximale autorisée pour le téléchargement en MB\nnet_maxfilesize 15\n\n\n\n// Équilibrage de Team //\n\n// Activer l\'équilibrage de l\'équipe\nmp_autoteambalance 1 \n\n// Temps après que les équipes soit déséquilibrer tente de changer les joueurs.\nmp_autoteambalance_delay 60\n\n// Temps après que les équipes soit déséquilibrer pour afficher un avertissement pour le rééquilibrage\nmp_autoteambalance_warning_delay 30\n\n// Teams are unbalanced when one team has this many more players than the other team. (0 disables check)\nmp_teams_unbalance_limit 1\n\n\n\n// Tour et temps de Jeu //\n\n// activer le timers pour attendre entre deux tours. AVERTISSEMENT: Un réglage à 0 a été connu pour causer un bug avec temps de préparation qui dure 5:20 (5 minutes 20 secondes) sur certains serveurs!\nmp_enableroundwaittime 1\n\n// Temps après un tour victorieux avant que le tour redémarre\nmp_bonusroundtime 8\n\n// Si non-zero, le tour actuel va redémarrer dans le nombre spécifié de secondes\nmp_restartround 0\n\n// Activer la mort subite\nmp_stalemate_enable 1\n\n// Limite de temps (en secondes) du tour.\nmp_stalemate_timelimit 300\n\n// temps de jeu par carte en minutes\nmp_timelimit 35\n\n\n\n// Client CVARS //\n\n// Limite au modes spectateurs pour les joueurs morts\nmp_forcecamera 0\n\n// permet de basculer le serveur en mode spectateur ou non\nmp_allowspectators 1\n\n// active le sont des pas\nmp_footsteps 1\n\n// active les triches du jeu\nsv_cheats 0\n\n// Après ce nombre de secondes sans un message d\'un client, le client est expulsé\nsv_timeout 900\n\n// Durée maximale d\'un joueur est autorisé à être inactif (en minutes), fait cela et sv_timeout parallèlement aussi?\nmp_idlemaxtime 15\n\n// Traite les joueurs inactifs  1=envoyer en spectateur 2=kick\nmp_idledealmethod 2\n\n// temps (en secondes) entre chaque sprays\ndecalfrequency 30\n\n\n\n// Communications //\n\n// permettre des communications vocales\nsv_voiceenable 1\n\n// Les joueurs peuvent entendre tous les autres joueurs, pas de restriction de team 0=off 1=on\nsv_alltalk 0\n\n// quantité de fois que les joueurs peuvent converser après le jeu est terminé\nmp_chattime 10\n'),
  (7,'Day of Defeat: Source','dod',1,'dod','srcds_run',232290,NULL,0,1,'dod_anzio',1,'','http://image.www.gametracker.com/images/maps/160x120/dods/','steam','// General server name, passwords and contact details\nhostname \"{{ hostname }}\"\nrcon_password \"{{ rconPassword }}\"\nsv_password \"{{ svPassword }}\"\nsv_contact \"\"\nsv_tags \"\"\nsv_region \"255\"\nsv_lan \"0\"\n\n// Server bans and server logs\nsv_rcon_banpenalty \"15\"\nsv_rcon_minfailures \"5\"\nsv_rcon_maxfailures \"10\"\nsv_rcon_minfailuretime \"30\"\nsv_rcon_maxpacketsize \"1024\"\nsv_rcon_maxpacketbans \"1\"\nlog \"on\"\nsv_logbans \"1\"\nsv_logecho \"1\"\nsv_logfile \"1\"\nsv_log_onefile \"0\"\nmp_logdetail \"3\"\n \n// Server downloads and files\n// No fast download maximum file size. 16 is def. 64 (max) recommended if you do not have a fast download server.\nnet_maxfilesize \"64\"\n//Fast download url. Leave blank if you dont have one but set the above to max.\nsv_downloadurl \"\"\nsv_allowdownload \"1\"\nsv_allowupload \"1\"\nsv_consistency \"1\"\nsv_pure \"2\"\nsv_pure_kick_clients \"0\"\nsv_pure_trace \"0\"\n\n// Bandwidth Rates\nsv_maxrate \"50000\"\nsv_minrate \"7500\"\nsv_maxupdaterate \"66\"\nsv_minupdaterate \"20\"\nsv_maxcmdrate \"66\"\nsv_mincmdrate \"30\"\n\n// General server settings\nmp_friendlyfire \"0\"\nsv_alltalk \"0\"\nmp_chattime \"10\"\nsv_use_steam_voice \"1\"\nsv_allow_voice_from_file \"0\"\nsv_voiceenable \"1\"\nmp_allowspectators \"1\"\nmp_timelimit \"25\"\ndecalfrequency \"10\"\nmp_flashlight \"1\"\nmp_footsteps \"1\"\nmp_autokick \"0\"\nmp_limitteams \"2\"\nsv_restrict_aspect_ratio_fov \"0\"\nmp_forcecamera \"1\"\nsv_cheats \"0\"\nsv_pausable \"0\"\nsv_allow_wait_command \"0\"\nmp_falldamage \"0\"\nmp_fadetoblack \"0\"\n\n// Movement speed and feel\nsv_gravity \"800\"\nsv_friction \"4\"\nsv_stopspeed \"100\"\n\n// DoD:S specific Gameplay and server settings\n//(ONLY found in DoD:S)\ndod_freezecam \"1\"\ndod_bonusround \"1\"\ndod_bonusroundtime \"15\"\n\n// Class Restrictions\nmp_allowrandomclass \"1\"\n\n// Allies Class Restrictions\nmp_limit_allies_rifleman \"-1\"\nmp_limit_allies_assault  \"-1\"\nmp_limit_allies_support \"-1\"\nmp_limit_allies_sniper \"-1\"\nmp_limit_allies_mg \"-1\"\nmp_limit_allies_rocket \"-1\"\n\n// Axis Class Restrictions \nmp_limit_axis_rifleman \"-1\"\nmp_limit_axis_assault  \"-1\"\nmp_limit_axis_support \"-1\"\nmp_limit_axis_sniper \"-1\"\nmp_limit_axis_mg \"-1\"\nmp_limit_axis_rocket \"-1\"\n \n// Execute ban files\nexec banned_user.cfg\nexec banned_ip.cfg\nwriteid\nwriteip\n'),
  (8,'Counter-Strike: Global Offensive','csgo',1,'csgo','srcds_run',740,NULL,0,1,'de_dust2',1,'','http://image.www.gametracker.com/images/maps/160x120/csgo/','steam','hostname \"{{ hostname }}\"\nrcon_password \"{{ rconPassword }}\"\nsv_password \"{{ svPassword }}\"\nlog on\n\nmp_freezetime 5 //The amount of time players are frozen to buy items\nmp_join_grace_time 15 //The amount of time players can join teams after a round has started\nsv_cheats 0 //This should always be set, so you know it\'s not on\nsv_lan 0 //This should always be set, so you know it\'s not on\n//**The bot commands below are mostly default with the exception of \nbot_difficulty 1\nbot_chatter \"off\"\nbot_join_after_player 1\nbot_quota 0\nbot_quota_mode \"fill\"\n//**The following commands manage kicks and bans\nwriteid\nwriteip\nexec banned_user.cfg\nexec banned_ip.cfg\n'),
  (9,'Minecraft','minecraft',0,'minecraft','minecraft_server.jar',NULL,NULL,0,0,'world',1,'./','http://image.www.gametracker.com/images/maps/160x120/minecraft/','minecraft','#Minecraft server properties\nlevel-name=world\nmotd={{ motd }}\nenable-rcon=true\nspawn-monsters=true\nwhite-list=false\nmax-players={{ maxPlayers }}\ngamemode=0\nallow-flight=false\ngenerate-structures=true\nspawn-animals=true\nspawn-npcs=true\nmax-build-height=256\ngenerator-settings=\ntexture-pack=\ndifficulty=1\nhardcore=false\nserver-port={{ serverPort }}\nforce-gamemode=false\nlevel-type=DEFAULT\nallow-nether=true\nenable-query=true\nview-distance=10\nonline-mode=true\nserver-ip={{ ip }}\npvp=true\nsnooper-enabled=true\nlevel-seed=\nrcon.password={{ rconPassword }}\nrcon.port={{ rconPort }}\nquery.port={{ queryPort }}'),
  (10,'Minecraft Bukkit','bukkit',0,'bukkit','craftbukkit.jar',NULL,NULL,0,0,'world',1,'./','http://image.www.gametracker.com/images/maps/160x120/minecraft/','minecraft','#Minecraft server properties\nlevel-name=world\nmotd={{ motd }}\nenable-rcon=true\nspawn-monsters=true\nwhite-list=false\nmax-players={{ maxPlayers }}\ngamemode=0\nallow-flight=false\ngenerate-structures=true\nspawn-animals=true\nspawn-npcs=true\nmax-build-height=256\ngenerator-settings=\ntexture-pack=\ndifficulty=1\nhardcore=false\nserver-port={{ serverPort }}\nforce-gamemode=false\nlevel-type=DEFAULT\nallow-nether=true\nenable-query=true\nview-distance=10\nonline-mode=true\nserver-ip={{ ip }}\npvp=true\nsnooper-enabled=true\nlevel-seed=\nrcon.password={{ rconPassword }}\nrcon.port={{ rconPort }}\nquery.port={{ queryPort }}')
;
UNLOCK TABLES;

LOCK TABLES `plugin` WRITE;
INSERT INTO `plugin` VALUES
  (1,'Metamod','http://www.dedicated-panel.net/metamod-1.21-am.tar.gz','metamod','N;','1.21-am'),
  (2,'AMX Mod X (Core Addon)','http://www.amxmodx.org/dl.php?file_id=690&mirror_id=2','amxmodx','N;','1.8.2'),
  (3,'AMX Mox X (Counter-Strike Addon)','http://www.amxmodx.org/dl.php?file_id=692&mirror_id=2','amxmodx','N;','1.8.2'),
  (4,'AMX Mod X (Day of Defeat Addon)','http://www.amxmodx.org/dl.php?file_id=694&mirror_id=2','amxmodx','N;','1.8.2'),
  (5,'AMX Mod X (Team Fortress Classic','http://www.amxmodx.org/dl.php?file_id=700&mirror_id=2','amxmodx','N;','1.8.2'),
  (6,'Metamod:Source','http://sourcemod.gameconnect.net/files/mmsource-1.10.0-linux.tar.gz','metamod_source','N;','1.10.0'),
  (7,'Sourcemod','http://sourcemod.gameconnect.net/files/sourcemod-1.5.0-linux.tar.gz','sourcemod','N;','1.5.0')
;
UNLOCK TABLES;

LOCK TABLES `game_plugin` WRITE;
INSERT INTO `game_plugin` VALUES
  (1,1),
  (1,2),
  (1,3),
  (2,1),
  (2,2),
  (2,3),
  (3,1),
  (3,2),
  (3,4),
  (4,1),
  (4,2),
  (4,5),
  (5,6),
  (5,7),
  (6,6),
  (6,7),
  (7,6),
  (7,7),
  (8,6),
  (8,7)
;
UNLOCK TABLES;
EOF;

        $this->addSql($sql);
    }
}

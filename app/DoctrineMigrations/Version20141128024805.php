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
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20141208094623 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Dropping old acl tables
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B806EA000B10');
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B8063D9AB4A6');
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B806DF9183C9');
        $this->addSql('ALTER TABLE acl_object_identities DROP FOREIGN KEY FK_9407E54977FA751A');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors DROP FOREIGN KEY FK_825DE299C671CEA1');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors DROP FOREIGN KEY FK_825DE2993D9AB4A6');
        $this->addSql('DROP TABLE acl_classes');
        $this->addSql('DROP TABLE acl_entries');
        $this->addSql('DROP TABLE acl_object_identities');
        $this->addSql('DROP TABLE acl_object_identity_ancestors');
        $this->addSql('DROP TABLE acl_security_identities');

        // Renaming & creating new user/group table
        $this->addSql('RENAME TABLE fos_user_user TO user_table');
        $this->addSql('CREATE TABLE group_table (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', root INT DEFAULT NULL, lvl INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, UNIQUE INDEX UNIQ_A605A4215E237E06 (name), INDEX IDX_A605A421727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_table ADD group_id INT DEFAULT NULL, ADD createdAt DATETIME DEFAULT NULL, ADD CONSTRAINT FK_14EB741EFE54D947 FOREIGN KEY (group_id) REFERENCES group_table (id), ADD INDEX IDX_14EB741EFE54D947 (group_id)');
        $this->addSql('ALTER TABLE group_table ADD CONSTRAINT FK_A605A421727ACA70 FOREIGN KEY (parent_id) REFERENCES group_table (id) ON DELETE CASCADE');
        // Replacing old index by new ones
        $this->addSql('CREATE UNIQUE INDEX UNIQ_14EB741E92FC23A8 ON user_table (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_14EB741EA0D96FBF ON user_table (email_canonical)');
        $this->addSql('DROP INDEX uniq_c560d76192fc23a8 ON user_table');
        $this->addSql('DROP INDEX uniq_c560d761a0d96fbf ON user_table');
        // Dropping old fos_user_{group,user_group} table
        $this->addSql('DROP TABLE fos_user_user_group');
        $this->addSql('DROP TABLE fos_user_group');

        // Updating existing tables
        $this->addSql('RENAME TABLE gameserver TO game_server');
        $this->addSql('ALTER TABLE game_server ADD core LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE installationstatus installation_status INT DEFAULT NULL');
        $this->addSql('ALTER TABLE machine DROP publicKey, CHANGE privateIp privateIp VARCHAR(15) NOT NULL, CHANGE home home VARCHAR(255) NOT NULL, CHANGE user username VARCHAR(16) NOT NULL, CHANGE privatekey privateKeyName VARCHAR(23) NOT NULL');
        $this->addSql('ALTER TABLE game CHANGE map map VARCHAR(40) DEFAULT NULL');
        $this->addSql('ALTER TABLE steam_server DROP core, DROP hltvPort');
        // Replacing old index by new ones
        $this->addSql('CREATE INDEX IDX_2758783E633EC4FD ON game_server (machineId)');
        $this->addSql('CREATE INDEX IDX_2758783EEC55B7A4 ON game_server (gameId)');
        $this->addSql('DROP INDEX idx_e26640e5633ec4fd ON game_server');
        $this->addSql('DROP INDEX idx_e26640e5ec55b7a4 ON game_server');

        // Creating the many-to-many join table between machine & group
        $this->addSql('CREATE TABLE machine_to_groups (machine_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_C2750E6EF6B75B26 (machine_id), INDEX IDX_C2750E6EFE54D947 (group_id), PRIMARY KEY(machine_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE machine_to_groups ADD CONSTRAINT FK_C2750E6EF6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id)');
        $this->addSql('ALTER TABLE machine_to_groups ADD CONSTRAINT FK_C2750E6EFE54D947 FOREIGN KEY (group_id) REFERENCES group_table (id)');

        // Adding voip & teamspeak tables
        $this->addSql('CREATE TABLE voip_server (id INT AUTO_INCREMENT NOT NULL, dir VARCHAR(64) NOT NULL, installation_status INT DEFAULT NULL, core LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', machineId INT DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_3F5E3DC5633EC4FD (machineId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voip_server_instance (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, port INT NOT NULL, max_clients INT NOT NULL, installation_status INT DEFAULT NULL, core LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', dir VARCHAR(64) NOT NULL, serverId INT DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_6AED3879EE279FF (serverId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teamspeak_server_instance (id INT NOT NULL, instance_id INT NOT NULL, admin_token VARCHAR(255) NOT NULL, autostart TINYINT(1) NOT NULL, banner LONGTEXT DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teamspeak_server (id INT NOT NULL, query_port INT DEFAULT NULL, query_login VARCHAR(32) DEFAULT NULL, query_passwd VARCHAR(32) DEFAULT NULL, filetransfer_port INT DEFAULT NULL, voice_port INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE voip_server ADD CONSTRAINT FK_3F5E3DC5633EC4FD FOREIGN KEY (machineId) REFERENCES machine (id)');
        $this->addSql('ALTER TABLE voip_server_instance ADD CONSTRAINT FK_6AED3879EE279FF FOREIGN KEY (serverId) REFERENCES voip_server (id)');
        $this->addSql('ALTER TABLE teamspeak_server_instance ADD CONSTRAINT FK_4B1CC3D6BF396750 FOREIGN KEY (id) REFERENCES voip_server_instance (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE teamspeak_server ADD CONSTRAINT FK_ACCB911EBF396750 FOREIGN KEY (id) REFERENCES voip_server (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Deleting voip & teamspeak tables
        $this->addSql('ALTER TABLE teamspeak_server DROP FOREIGN KEY FK_ACCB911EBF396750');
        $this->addSql('ALTER TABLE voip_server_instance DROP FOREIGN KEY FK_6AED3879EE279FF');
        $this->addSql('ALTER TABLE teamspeak_server_instance DROP FOREIGN KEY FK_4B1CC3D6BF396750');
        $this->addSql('DROP TABLE teamspeak_server');
        $this->addSql('DROP TABLE teamspeak_server_instance');
        $this->addSql('DROP TABLE voip_server');
        $this->addSql('DROP TABLE voip_server_instance');

        // Deleting the many-to-many join table between machine & group
        $this->addSql('DROP TABLE machine_to_groups');

        // Reverting updates of existing tables
        $this->addSql('ALTER TABLE game CHANGE map map VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE machine ADD publicKey VARCHAR(255) DEFAULT NULL, CHANGE privateIp privateIp VARCHAR(15) DEFAULT NULL, CHANGE home home VARCHAR(255) DEFAULT NULL, CHANGE username user VARCHAR(16) NOT NULL, CHANGE privatekeyname privateKey VARCHAR(23) NOT NULL');
        $this->addSql('RENAME TABLE game_server TO gameserver');
        $this->addSql('ALTER TABLE gameserver DROP core, CHANGE installation_status installationStatus INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gameserver_plugins DROP FOREIGN KEY FK_9B4F31881844E6B7');
        $this->addSql('ALTER TABLE gameserver_plugins ADD CONSTRAINT FK_9B4F31881844E6B7 FOREIGN KEY (server_id) REFERENCES gameserver (id)');
        $this->addSql('ALTER TABLE steam_server ADD core INT DEFAULT NULL, ADD hltvPort INT DEFAULT NULL');

        // Restoring old fos_user_{user,group,user_group} tables
        $this->addSql('ALTER TABLE user_table DROP FOREIGN KEY FK_14EB741EFE54D947');
        $this->addSql('ALTER TABLE group_table DROP FOREIGN KEY FK_A605A421727ACA70');
        $this->addSql('RENAME TABLE user_table TO fos_user_user');
        $this->addSql('ALTER TABLE fos_user_user DROP group_id, DROP createdAt, DROP INDEX IDX_14EB741EFE54D947');
        $this->addSql('CREATE TABLE fos_user_user_group (user_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_B3C77447A76ED395 (user_id), INDEX IDX_B3C77447FE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_583D1F3E5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447FE54D947 FOREIGN KEY (group_id) REFERENCES fos_user_group (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE group_table');

        // Restoring acl tables
        $this->addSql('CREATE TABLE acl_classes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_type VARCHAR(200) NOT NULL, UNIQUE INDEX UNIQ_69DD750638A36066 (class_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_security_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, identifier VARCHAR(200) NOT NULL, username TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8835EE78772E836AF85E0677 (identifier, username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_object_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, parent_object_identity_id INT UNSIGNED DEFAULT NULL, class_id INT UNSIGNED NOT NULL, object_identifier VARCHAR(100) NOT NULL, entries_inheriting TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_9407E5494B12AD6EA000B10 (object_identifier, class_id), INDEX IDX_9407E54977FA751A (parent_object_identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_object_identity_ancestors (object_identity_id INT UNSIGNED NOT NULL, ancestor_id INT UNSIGNED NOT NULL, INDEX IDX_825DE2993D9AB4A6 (object_identity_id), INDEX IDX_825DE299C671CEA1 (ancestor_id), PRIMARY KEY(object_identity_id, ancestor_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_entries (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_id INT UNSIGNED NOT NULL, object_identity_id INT UNSIGNED DEFAULT NULL, security_identity_id INT UNSIGNED NOT NULL, field_name VARCHAR(50) DEFAULT NULL, ace_order SMALLINT UNSIGNED NOT NULL, mask INT NOT NULL, granting TINYINT(1) NOT NULL, granting_strategy VARCHAR(30) NOT NULL, audit_success TINYINT(1) NOT NULL, audit_failure TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_46C8B806EA000B103D9AB4A64DEF17BCE4289BF4 (class_id, object_identity_id, field_name, ace_order), INDEX IDX_46C8B806EA000B103D9AB4A6DF9183C9 (class_id, object_identity_id, security_identity_id), INDEX IDX_46C8B806EA000B10 (class_id), INDEX IDX_46C8B8063D9AB4A6 (object_identity_id), INDEX IDX_46C8B806DF9183C9 (security_identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        // Restoring foreign keys
        $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B806EA000B10 FOREIGN KEY (class_id) REFERENCES acl_classes (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B8063D9AB4A6 FOREIGN KEY (object_identity_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B806DF9183C9 FOREIGN KEY (security_identity_id) REFERENCES acl_security_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_object_identities ADD CONSTRAINT FK_9407E54977FA751A FOREIGN KEY (parent_object_identity_id) REFERENCES acl_object_identities (id)');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors ADD CONSTRAINT FK_825DE2993D9AB4A6 FOREIGN KEY (object_identity_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors ADD CONSTRAINT FK_825DE299C671CEA1 FOREIGN KEY (ancestor_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
    }
}

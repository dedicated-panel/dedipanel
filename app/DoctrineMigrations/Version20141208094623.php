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
        $this->addSql('ALTER TABLE acl_object_identities DROP FOREIGN KEY FK_9407E54977FA751A');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors DROP FOREIGN KEY FK_825DE299C671CEA1');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors DROP FOREIGN KEY FK_825DE2993D9AB4A6');
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B806DF9183C9');
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

        // Create the many-to-many join table between machine & group
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
    }
}

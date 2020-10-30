<?php

declare(strict_types=1);

namespace Tranquillity\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201023005101 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tql_auth_authorisation_codes ADD CONSTRAINT FK_D8C3BB9EEA1CE9BE FOREIGN KEY (clientId) REFERENCES tql_auth_clients (id)');
        $this->addSql('ALTER TABLE tql_auth_authorisation_codes ADD CONSTRAINT FK_D8C3BB9E64B64DCC FOREIGN KEY (userId) REFERENCES tql_bus_users (id)');
        $this->addSql('ALTER TABLE tql_auth_clients DROP grantTypes, DROP scope, DROP userId, CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE clientName clientName VARCHAR(255) NOT NULL, CHANGE clientSecret clientSecret VARCHAR(255) NOT NULL, CHANGE redirectUri redirectUri VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE tql_auth_scopes CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE scope scope VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE tql_auth_tokens_access CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE clientId clientId BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE userId userId BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\'');
        $this->addSql('ALTER TABLE tql_auth_tokens_access ADD CONSTRAINT FK_965A1DF4EA1CE9BE FOREIGN KEY (clientId) REFERENCES tql_auth_clients (id)');
        $this->addSql('ALTER TABLE tql_auth_tokens_access ADD CONSTRAINT FK_965A1DF464B64DCC FOREIGN KEY (userId) REFERENCES tql_bus_users (id)');
        $this->addSql('CREATE INDEX IDX_965A1DF4EA1CE9BE ON tql_auth_tokens_access (clientId)');
        $this->addSql('CREATE INDEX IDX_965A1DF464B64DCC ON tql_auth_tokens_access (userId)');
        $this->addSql('ALTER TABLE tql_auth_tokens_refresh CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE clientId clientId BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE userId userId BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\'');
        $this->addSql('ALTER TABLE tql_auth_tokens_refresh ADD CONSTRAINT FK_B6C73025EA1CE9BE FOREIGN KEY (clientId) REFERENCES tql_auth_clients (id)');
        $this->addSql('ALTER TABLE tql_auth_tokens_refresh ADD CONSTRAINT FK_B6C7302564B64DCC FOREIGN KEY (userId) REFERENCES tql_bus_users (id)');
        $this->addSql('CREATE INDEX IDX_B6C73025EA1CE9BE ON tql_auth_tokens_refresh (clientId)');
        $this->addSql('CREATE INDEX IDX_B6C7302564B64DCC ON tql_auth_tokens_refresh (userId)');
        $this->addSql('ALTER TABLE tql_bus_accounts CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\'');
        $this->addSql('ALTER TABLE tql_bus_accounts ADD CONSTRAINT FK_84FAB410BF396750 FOREIGN KEY (id) REFERENCES tql_bus_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tql_bus_entity DROP subType, CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE type type VARCHAR(255) NOT NULL, CHANGE transactionId transactionId BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\'');
        $this->addSql('ALTER TABLE tql_bus_entity ADD CONSTRAINT FK_A48A50C2C2F43114 FOREIGN KEY (transactionId) REFERENCES tql_sys_audit_txn (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A48A50C2C2F43114 ON tql_bus_entity (transactionId)');
        $this->addSql('ALTER TABLE tql_xref_entity_tags CHANGE entityId entityId BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE tagId tagId BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\'');
        $this->addSql('ALTER TABLE tql_xref_entity_tags ADD CONSTRAINT FK_F0AE8EB4F62829FC FOREIGN KEY (entityId) REFERENCES tql_bus_entity (id)');
        $this->addSql('ALTER TABLE tql_xref_entity_tags ADD CONSTRAINT FK_F0AE8EB46F16ADDC FOREIGN KEY (tagId) REFERENCES tql_sys_tags (id)');
        $this->addSql('CREATE INDEX IDX_F0AE8EB4F62829FC ON tql_xref_entity_tags (entityId)');
        $this->addSql('CREATE INDEX IDX_F0AE8EB46F16ADDC ON tql_xref_entity_tags (tagId)');
        $this->addSql('ALTER TABLE tql_bus_people CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE title title VARCHAR(255) DEFAULT NULL, CHANGE userId userId BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\'');
        $this->addSql('ALTER TABLE tql_bus_people ADD CONSTRAINT FK_82B47E8C64B64DCC FOREIGN KEY (userId) REFERENCES tql_bus_users (id)');
        $this->addSql('ALTER TABLE tql_bus_people ADD CONSTRAINT FK_82B47E8CBF396750 FOREIGN KEY (id) REFERENCES tql_bus_entity (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_82B47E8C64B64DCC ON tql_bus_people (userId)');
        $this->addSql('ALTER TABLE tql_bus_users CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE timezoneCode timezoneCode VARCHAR(255) NOT NULL, CHANGE localeCode localeCode VARCHAR(255) NOT NULL, CHANGE securityGroupId securityGroupId INT NOT NULL');
        $this->addSql('ALTER TABLE tql_bus_users ADD CONSTRAINT FK_BCDB5087BF396750 FOREIGN KEY (id) REFERENCES tql_bus_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tql_ref_countries CHANGE code code VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE effectiveUntil effectiveUntil DATETIME NOT NULL');
        $this->addSql('ALTER TABLE tql_ref_locales CHANGE code code VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE effectiveUntil effectiveUntil DATETIME NOT NULL');
        $this->addSql('ALTER TABLE tql_ref_timezones CHANGE code code VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE effectiveUntil effectiveUntil DATETIME NOT NULL');
        $this->addSql('ALTER TABLE tql_sys_audit_txn CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE clientId clientId BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE userId userId BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE updateReason updateReason VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE tql_sys_audit_txn ADD CONSTRAINT FK_3689F15C64B64DCC FOREIGN KEY (userId) REFERENCES tql_bus_users (id)');
        $this->addSql('ALTER TABLE tql_sys_audit_txn ADD CONSTRAINT FK_3689F15CEA1CE9BE FOREIGN KEY (clientId) REFERENCES tql_auth_clients (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3689F15C64B64DCC ON tql_sys_audit_txn (userId)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3689F15CEA1CE9BE ON tql_sys_audit_txn (clientId)');
        $this->addSql('ALTER TABLE tql_sys_audit_txn_fields DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE tql_sys_audit_txn_fields CHANGE transactionId transactionId BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE entityId entityId BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', CHANGE fieldName fieldName VARCHAR(255) NOT NULL, CHANGE dataType dataType VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE tql_sys_audit_txn_fields ADD CONSTRAINT FK_D4DB38EAF62829FC FOREIGN KEY (entityId) REFERENCES tql_bus_entity (id)');
        $this->addSql('ALTER TABLE tql_sys_audit_txn_fields ADD CONSTRAINT FK_D4DB38EAC2F43114 FOREIGN KEY (transactionId) REFERENCES tql_sys_audit_txn (id)');
        $this->addSql('CREATE INDEX IDX_D4DB38EAF62829FC ON tql_sys_audit_txn_fields (entityId)');
        $this->addSql('CREATE INDEX IDX_D4DB38EAC2F43114 ON tql_sys_audit_txn_fields (transactionId)');
        $this->addSql('ALTER TABLE tql_sys_audit_txn_fields ADD PRIMARY KEY (fieldName, entityId, transactionId)');
        $this->addSql('ALTER TABLE tql_sys_tags CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tql_auth_authorisation_codes DROP FOREIGN KEY FK_D8C3BB9EEA1CE9BE');
        $this->addSql('ALTER TABLE tql_auth_authorisation_codes DROP FOREIGN KEY FK_D8C3BB9E64B64DCC');
        $this->addSql('ALTER TABLE tql_auth_clients ADD grantTypes VARCHAR(80) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, ADD scope TINYTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, ADD userId BINARY(16) DEFAULT NULL, CHANGE id id BINARY(16) NOT NULL, CHANGE clientName clientName VARCHAR(80) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE clientSecret clientSecret VARCHAR(80) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, CHANGE redirectUri redirectUri TINYTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`');
        $this->addSql('ALTER TABLE tql_auth_scopes CHANGE id id BINARY(16) NOT NULL, CHANGE scope scope VARCHAR(80) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`');
        $this->addSql('ALTER TABLE tql_auth_tokens_access DROP FOREIGN KEY FK_965A1DF4EA1CE9BE');
        $this->addSql('ALTER TABLE tql_auth_tokens_access DROP FOREIGN KEY FK_965A1DF464B64DCC');
        $this->addSql('DROP INDEX IDX_965A1DF4EA1CE9BE ON tql_auth_tokens_access');
        $this->addSql('DROP INDEX IDX_965A1DF464B64DCC ON tql_auth_tokens_access');
        $this->addSql('ALTER TABLE tql_auth_tokens_access CHANGE id id BINARY(16) NOT NULL, CHANGE clientId clientId BINARY(16) NOT NULL, CHANGE userId userId BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE tql_auth_tokens_refresh DROP FOREIGN KEY FK_B6C73025EA1CE9BE');
        $this->addSql('ALTER TABLE tql_auth_tokens_refresh DROP FOREIGN KEY FK_B6C7302564B64DCC');
        $this->addSql('DROP INDEX IDX_B6C73025EA1CE9BE ON tql_auth_tokens_refresh');
        $this->addSql('DROP INDEX IDX_B6C7302564B64DCC ON tql_auth_tokens_refresh');
        $this->addSql('ALTER TABLE tql_auth_tokens_refresh CHANGE id id BINARY(16) NOT NULL, CHANGE clientId clientId BINARY(16) NOT NULL, CHANGE userId userId BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE tql_bus_accounts DROP FOREIGN KEY FK_84FAB410BF396750');
        $this->addSql('ALTER TABLE tql_bus_accounts CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE tql_bus_entity DROP FOREIGN KEY FK_A48A50C2C2F43114');
        $this->addSql('DROP INDEX UNIQ_A48A50C2C2F43114 ON tql_bus_entity');
        $this->addSql('ALTER TABLE tql_bus_entity ADD subType VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, CHANGE id id BINARY(16) NOT NULL, CHANGE transactionId transactionId BINARY(16) NOT NULL, CHANGE type type VARCHAR(25) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`');
        $this->addSql('ALTER TABLE tql_bus_people DROP FOREIGN KEY FK_82B47E8C64B64DCC');
        $this->addSql('ALTER TABLE tql_bus_people DROP FOREIGN KEY FK_82B47E8CBF396750');
        $this->addSql('DROP INDEX UNIQ_82B47E8C64B64DCC ON tql_bus_people');
        $this->addSql('ALTER TABLE tql_bus_people CHANGE id id BINARY(16) NOT NULL, CHANGE title title VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, CHANGE userId userId BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE tql_bus_users DROP FOREIGN KEY FK_BCDB5087BF396750');
        $this->addSql('ALTER TABLE tql_bus_users CHANGE id id BINARY(16) NOT NULL, CHANGE timezoneCode timezoneCode VARCHAR(30) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE localeCode localeCode VARCHAR(30) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE securityGroupId securityGroupId BIGINT NOT NULL');
        $this->addSql('ALTER TABLE tql_ref_countries CHANGE code code VARCHAR(30) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE description description VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE effectiveUntil effectiveUntil DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tql_ref_locales CHANGE code code VARCHAR(30) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE description description VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE effectiveUntil effectiveUntil DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tql_ref_timezones CHANGE code code VARCHAR(30) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE description description VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE effectiveUntil effectiveUntil DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tql_sys_audit_txn DROP FOREIGN KEY FK_3689F15C64B64DCC');
        $this->addSql('ALTER TABLE tql_sys_audit_txn DROP FOREIGN KEY FK_3689F15CEA1CE9BE');
        $this->addSql('DROP INDEX UNIQ_3689F15C64B64DCC ON tql_sys_audit_txn');
        $this->addSql('DROP INDEX UNIQ_3689F15CEA1CE9BE ON tql_sys_audit_txn');
        $this->addSql('ALTER TABLE tql_sys_audit_txn CHANGE id id BINARY(16) NOT NULL, CHANGE updateReason updateReason VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE userId userId BINARY(16) DEFAULT NULL, CHANGE clientId clientId BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE tql_sys_audit_txn_fields DROP FOREIGN KEY FK_D4DB38EAF62829FC');
        $this->addSql('ALTER TABLE tql_sys_audit_txn_fields DROP FOREIGN KEY FK_D4DB38EAC2F43114');
        $this->addSql('DROP INDEX IDX_D4DB38EAF62829FC ON tql_sys_audit_txn_fields');
        $this->addSql('DROP INDEX IDX_D4DB38EAC2F43114 ON tql_sys_audit_txn_fields');
        $this->addSql('ALTER TABLE tql_sys_audit_txn_fields DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE tql_sys_audit_txn_fields CHANGE fieldName fieldName VARCHAR(45) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE dataType dataType VARCHAR(45) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE entityId entityId BINARY(16) NOT NULL, CHANGE transactionId transactionId BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE tql_sys_audit_txn_fields ADD PRIMARY KEY (transactionId, entityId, fieldName)');
        $this->addSql('ALTER TABLE tql_sys_tags CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE tql_xref_entity_tags DROP FOREIGN KEY FK_F0AE8EB4F62829FC');
        $this->addSql('ALTER TABLE tql_xref_entity_tags DROP FOREIGN KEY FK_F0AE8EB46F16ADDC');
        $this->addSql('DROP INDEX IDX_F0AE8EB4F62829FC ON tql_xref_entity_tags');
        $this->addSql('DROP INDEX IDX_F0AE8EB46F16ADDC ON tql_xref_entity_tags');
        $this->addSql('ALTER TABLE tql_xref_entity_tags CHANGE entityId entityId BINARY(16) NOT NULL, CHANGE tagId tagId BINARY(16) NOT NULL');
    }
}

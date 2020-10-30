<?php

declare(strict_types=1);

namespace Tranquillity\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201023004849 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_auth_authorisation_codes (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, clientId BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', userId BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', redirectUri VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, expires DATETIME NOT NULL, scope VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_D8C3BB9E64B64DCC (userId), INDEX IDX_D8C3BB9EEA1CE9BE (clientId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_auth_clients (id BINARY(16) NOT NULL, clientName VARCHAR(80) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, clientSecret VARCHAR(80) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, redirectUri TINYTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, grantTypes VARCHAR(80) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, scope TINYTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, userId BINARY(16) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_auth_scopes (id BINARY(16) NOT NULL, scope VARCHAR(80) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, isDefault TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_auth_tokens_access (id BINARY(16) NOT NULL, token VARCHAR(40) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, clientId BINARY(16) NOT NULL, userId BINARY(16) DEFAULT NULL, expires DATETIME NOT NULL, scope VARCHAR(4000) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_auth_tokens_refresh (id BINARY(16) NOT NULL, token VARCHAR(40) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, clientId BINARY(16) NOT NULL, userId BINARY(16) DEFAULT NULL, expires DATETIME NOT NULL, scope VARCHAR(4000) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_bus_accounts (id BINARY(16) NOT NULL, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_bus_entity (id BINARY(16) NOT NULL, version INT NOT NULL, type VARCHAR(25) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, subType VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, deleted TINYINT(1) NOT NULL, transactionId BINARY(16) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_bus_people (id BINARY(16) NOT NULL, title VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, firstName VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, lastName VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, position VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, userId BINARY(16) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_bus_users (id BINARY(16) NOT NULL, username VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, password VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, timezoneCode VARCHAR(30) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, localeCode VARCHAR(30) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, active TINYINT(1) NOT NULL, securityGroupId BIGINT NOT NULL, registeredDateTime DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_ref_countries (code VARCHAR(30) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, description VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, ordering INT NOT NULL, effectiveFrom DATETIME NOT NULL, effectiveUntil DATETIME DEFAULT NULL, PRIMARY KEY(code)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_ref_locales (code VARCHAR(30) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, description VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, ordering INT NOT NULL, effectiveFrom DATETIME NOT NULL, effectiveUntil DATETIME DEFAULT NULL, PRIMARY KEY(code)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_ref_timezones (code VARCHAR(30) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, description VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, daylightSavings TINYINT(1) NOT NULL, ordering INT NOT NULL, effectiveFrom DATETIME NOT NULL, effectiveUntil DATETIME DEFAULT NULL, PRIMARY KEY(code)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_sys_audit_txn (id BINARY(16) NOT NULL, clientId BINARY(16) NOT NULL, userId BINARY(16) DEFAULT NULL, timestamp DATETIME NOT NULL, updateReason VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_sys_audit_txn_fields (transactionId BINARY(16) NOT NULL, entityId BINARY(16) NOT NULL, fieldName VARCHAR(45) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, dataType VARCHAR(45) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, oldValue VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, newValue VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, PRIMARY KEY(transactionId, entityId, fieldName)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_sys_tags (id BINARY(16) NOT NULL, label VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tql_xref_entity_tags (entityId BINARY(16) NOT NULL, tagId BINARY(16) NOT NULL, PRIMARY KEY(entityId, tagId)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_auth_authorisation_codes');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_auth_clients');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_auth_scopes');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_auth_tokens_access');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_auth_tokens_refresh');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_bus_accounts');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_bus_entity');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_bus_people');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_bus_users');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_ref_countries');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_ref_locales');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_ref_timezones');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_sys_audit_txn');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_sys_audit_txn_fields');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_sys_tags');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tql_xref_entity_tags');
    }
}

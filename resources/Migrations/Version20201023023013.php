<?php

declare(strict_types=1);

namespace Tranquillity\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201023023013 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tql_ref_countries CHANGE effectiveUntil effectiveUntil DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tql_ref_locales CHANGE effectiveUntil effectiveUntil DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tql_ref_timezones CHANGE effectiveUntil effectiveUntil DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tql_ref_countries CHANGE effectiveUntil effectiveUntil DATETIME NOT NULL');
        $this->addSql('ALTER TABLE tql_ref_locales CHANGE effectiveUntil effectiveUntil DATETIME NOT NULL');
        $this->addSql('ALTER TABLE tql_ref_timezones CHANGE effectiveUntil effectiveUntil DATETIME NOT NULL');
    }
}

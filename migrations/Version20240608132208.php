<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240608132208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__achat AS SELECT id, dateachat, numfacture, created_at FROM achat');
        $this->addSql('DROP TABLE achat');
        $this->addSql('CREATE TABLE achat (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, dateachat DATETIME NOT NULL, numfacture NUMERIC(10, 0) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('INSERT INTO achat (id, dateachat, numfacture, created_at) SELECT id, dateachat, numfacture, created_at FROM __temp__achat');
        $this->addSql('DROP TABLE __temp__achat');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE achat ADD COLUMN datefacture DATETIME NOT NULL');
    }
}

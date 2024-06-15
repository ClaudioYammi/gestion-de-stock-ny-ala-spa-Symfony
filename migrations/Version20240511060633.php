<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240511060633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__inventaire AS SELECT id, reference_id, update_at, note, stockinventaire, ecart, stockutiliser FROM inventaire');
        $this->addSql('DROP TABLE inventaire');
        $this->addSql('CREATE TABLE inventaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference_id INTEGER NOT NULL, update_at DATETIME NOT NULL, note VARCHAR(255) NOT NULL, stockinventaire NUMERIC(10, 0) NOT NULL, ecart NUMERIC(10, 0) NOT NULL, stockutiliser NUMERIC(10, 0) NOT NULL, CONSTRAINT FK_338920E01645DEA9 FOREIGN KEY (reference_id) REFERENCES produit (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO inventaire (id, reference_id, update_at, note, stockinventaire, ecart, stockutiliser) SELECT id, reference_id, update_at, note, stockinventaire, ecart, stockutiliser FROM __temp__inventaire');
        $this->addSql('DROP TABLE __temp__inventaire');
        $this->addSql('CREATE INDEX IDX_338920E01645DEA9 ON inventaire (reference_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__inventaire AS SELECT id, reference_id, update_at, note, stockinventaire, ecart, stockutiliser FROM inventaire');
        $this->addSql('DROP TABLE inventaire');
        $this->addSql('CREATE TABLE inventaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference_id INTEGER NOT NULL, update_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , note VARCHAR(255) NOT NULL, stockinventaire NUMERIC(10, 0) NOT NULL, ecart NUMERIC(10, 0) NOT NULL, stockutiliser NUMERIC(10, 0) NOT NULL, CONSTRAINT FK_338920E01645DEA9 FOREIGN KEY (reference_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO inventaire (id, reference_id, update_at, note, stockinventaire, ecart, stockutiliser) SELECT id, reference_id, update_at, note, stockinventaire, ecart, stockutiliser FROM __temp__inventaire');
        $this->addSql('DROP TABLE __temp__inventaire');
        $this->addSql('CREATE INDEX IDX_338920E01645DEA9 ON inventaire (reference_id)');
    }
}

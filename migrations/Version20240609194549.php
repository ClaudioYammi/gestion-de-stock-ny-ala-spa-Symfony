<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240609194549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE achat ADD COLUMN tva NUMERIC(10, 0) NOT NULL');
        $this->addSql('ALTER TABLE achat ADD COLUMN remise NUMERIC(10, 0) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__achat AS SELECT id, id_fournisseur_id, dateachat, numfacture, created_at FROM achat');
        $this->addSql('DROP TABLE achat');
        $this->addSql('CREATE TABLE achat (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_fournisseur_id INTEGER NOT NULL, dateachat DATETIME NOT NULL, numfacture NUMERIC(10, 0) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_26A984565A6AC879 FOREIGN KEY (id_fournisseur_id) REFERENCES fournisseur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO achat (id, id_fournisseur_id, dateachat, numfacture, created_at) SELECT id, id_fournisseur_id, dateachat, numfacture, created_at FROM __temp__achat');
        $this->addSql('DROP TABLE __temp__achat');
        $this->addSql('CREATE INDEX IDX_26A984565A6AC879 ON achat (id_fournisseur_id)');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240511053401 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE detail_inventaire');
        $this->addSql('DROP TABLE inventaire');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE detail_inventaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_inventaire_id INTEGER NOT NULL, id_produit_id INTEGER NOT NULL, quantite NUMERIC(10, 0) NOT NULL, CONSTRAINT FK_88939036706BDBE0 FOREIGN KEY (id_inventaire_id) REFERENCES inventaire (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_88939036AABEFE2C FOREIGN KEY (id_produit_id) REFERENCES produit (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_88939036AABEFE2C ON detail_inventaire (id_produit_id)');
        $this->addSql('CREATE INDEX IDX_88939036706BDBE0 ON detail_inventaire (id_inventaire_id)');
        $this->addSql('CREATE TABLE inventaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, update_at DATETIME NOT NULL, note VARCHAR(255) NOT NULL COLLATE "BINARY")');
    }
}

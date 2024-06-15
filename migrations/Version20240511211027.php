<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240511211027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit ADD COLUMN image_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE produit ADD COLUMN updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__produit AS SELECT id, id_fournisseur_id, id_categorie_id, emplacement_id, designation, description, prixunitaire, dateexp, qttemin, qttemax, capaciter, unite FROM produit');
        $this->addSql('DROP TABLE produit');
        $this->addSql('CREATE TABLE produit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_fournisseur_id INTEGER NOT NULL, id_categorie_id INTEGER NOT NULL, emplacement_id INTEGER NOT NULL, designation VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, prixunitaire NUMERIC(10, 2) NOT NULL, dateexp DATETIME NOT NULL, qttemin NUMERIC(10, 0) NOT NULL, qttemax NUMERIC(10, 0) NOT NULL, capaciter VARCHAR(255) NOT NULL, unite VARCHAR(255) NOT NULL, CONSTRAINT FK_29A5EC275A6AC879 FOREIGN KEY (id_fournisseur_id) REFERENCES fournisseur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_29A5EC279F34925F FOREIGN KEY (id_categorie_id) REFERENCES categorie (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_29A5EC27C4598A51 FOREIGN KEY (emplacement_id) REFERENCES emplacement (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO produit (id, id_fournisseur_id, id_categorie_id, emplacement_id, designation, description, prixunitaire, dateexp, qttemin, qttemax, capaciter, unite) SELECT id, id_fournisseur_id, id_categorie_id, emplacement_id, designation, description, prixunitaire, dateexp, qttemin, qttemax, capaciter, unite FROM __temp__produit');
        $this->addSql('DROP TABLE __temp__produit');
        $this->addSql('CREATE INDEX IDX_29A5EC275A6AC879 ON produit (id_fournisseur_id)');
        $this->addSql('CREATE INDEX IDX_29A5EC279F34925F ON produit (id_categorie_id)');
        $this->addSql('CREATE INDEX IDX_29A5EC27C4598A51 ON produit (emplacement_id)');
    }
}

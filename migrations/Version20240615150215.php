<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240615150215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__detail_vente AS SELECT id, reference_id, id_vente_id, quantite, prixunitaire FROM detail_vente');
        $this->addSql('DROP TABLE detail_vente');
        $this->addSql('CREATE TABLE detail_vente (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference_id INTEGER NOT NULL, id_vente_id INTEGER NOT NULL, quantite NUMERIC(10, 0) NOT NULL, prixunitairevente NUMERIC(10, 2) NOT NULL, CONSTRAINT FK_F57AE1151645DEA9 FOREIGN KEY (reference_id) REFERENCES produit (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F57AE1152D1CFB9F FOREIGN KEY (id_vente_id) REFERENCES vente (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO detail_vente (id, reference_id, id_vente_id, quantite, prixunitairevente) SELECT id, reference_id, id_vente_id, quantite, prixunitaire FROM __temp__detail_vente');
        $this->addSql('DROP TABLE __temp__detail_vente');
        $this->addSql('CREATE INDEX IDX_F57AE1152D1CFB9F ON detail_vente (id_vente_id)');
        $this->addSql('CREATE INDEX IDX_F57AE1151645DEA9 ON detail_vente (reference_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__produit AS SELECT id, id_categorie_id, emplacement_id, designation, description, prixunitaire, dateexp, qttemin, qttemax, capaciter, unite, image_name, updated_at, prixunitairevente FROM produit');
        $this->addSql('DROP TABLE produit');
        $this->addSql('CREATE TABLE produit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_categorie_id INTEGER NOT NULL, emplacement_id INTEGER NOT NULL, designation VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, prixunitaire NUMERIC(10, 2) NOT NULL, dateexp DATETIME NOT NULL, qttemin NUMERIC(10, 0) NOT NULL, qttemax NUMERIC(10, 0) NOT NULL, capaciter VARCHAR(255) NOT NULL, unite VARCHAR(255) NOT NULL, image_name VARCHAR(255) NOT NULL, updated_at DATETIME DEFAULT NULL, prixunitairevente NUMERIC(10, 2) NOT NULL, CONSTRAINT FK_29A5EC279F34925F FOREIGN KEY (id_categorie_id) REFERENCES categorie (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_29A5EC27C4598A51 FOREIGN KEY (emplacement_id) REFERENCES emplacement (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO produit (id, id_categorie_id, emplacement_id, designation, description, prixunitaire, dateexp, qttemin, qttemax, capaciter, unite, image_name, updated_at, prixunitairevente) SELECT id, id_categorie_id, emplacement_id, designation, description, prixunitaire, dateexp, qttemin, qttemax, capaciter, unite, image_name, updated_at, prixunitairevente FROM __temp__produit');
        $this->addSql('DROP TABLE __temp__produit');
        $this->addSql('CREATE INDEX IDX_29A5EC279F34925F ON produit (id_categorie_id)');
        $this->addSql('CREATE INDEX IDX_29A5EC27C4598A51 ON produit (emplacement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__detail_vente AS SELECT id, reference_id, id_vente_id, quantite, prixunitairevente FROM detail_vente');
        $this->addSql('DROP TABLE detail_vente');
        $this->addSql('CREATE TABLE detail_vente (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference_id INTEGER NOT NULL, id_vente_id INTEGER NOT NULL, quantite NUMERIC(10, 0) NOT NULL, prixunitaire NUMERIC(10, 2) NOT NULL, CONSTRAINT FK_F57AE1151645DEA9 FOREIGN KEY (reference_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F57AE1152D1CFB9F FOREIGN KEY (id_vente_id) REFERENCES vente (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO detail_vente (id, reference_id, id_vente_id, quantite, prixunitaire) SELECT id, reference_id, id_vente_id, quantite, prixunitairevente FROM __temp__detail_vente');
        $this->addSql('DROP TABLE __temp__detail_vente');
        $this->addSql('CREATE INDEX IDX_F57AE1151645DEA9 ON detail_vente (reference_id)');
        $this->addSql('CREATE INDEX IDX_F57AE1152D1CFB9F ON detail_vente (id_vente_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__produit AS SELECT id, id_categorie_id, emplacement_id, designation, description, prixunitaire, dateexp, qttemin, qttemax, capaciter, unite, image_name, updated_at, prixunitairevente FROM produit');
        $this->addSql('DROP TABLE produit');
        $this->addSql('CREATE TABLE produit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_categorie_id INTEGER NOT NULL, emplacement_id INTEGER NOT NULL, designation VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, prixunitaire NUMERIC(10, 2) NOT NULL, dateexp DATETIME NOT NULL, qttemin NUMERIC(10, 0) NOT NULL, qttemax NUMERIC(10, 0) NOT NULL, capaciter VARCHAR(255) NOT NULL, unite VARCHAR(255) NOT NULL, image_name VARCHAR(255) NOT NULL, updated_at DATETIME DEFAULT NULL, prixunitairevente NUMERIC(10, 0) NOT NULL, CONSTRAINT FK_29A5EC279F34925F FOREIGN KEY (id_categorie_id) REFERENCES categorie (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_29A5EC27C4598A51 FOREIGN KEY (emplacement_id) REFERENCES emplacement (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO produit (id, id_categorie_id, emplacement_id, designation, description, prixunitaire, dateexp, qttemin, qttemax, capaciter, unite, image_name, updated_at, prixunitairevente) SELECT id, id_categorie_id, emplacement_id, designation, description, prixunitaire, dateexp, qttemin, qttemax, capaciter, unite, image_name, updated_at, prixunitairevente FROM __temp__produit');
        $this->addSql('DROP TABLE __temp__produit');
        $this->addSql('CREATE INDEX IDX_29A5EC279F34925F ON produit (id_categorie_id)');
        $this->addSql('CREATE INDEX IDX_29A5EC27C4598A51 ON produit (emplacement_id)');
    }
}

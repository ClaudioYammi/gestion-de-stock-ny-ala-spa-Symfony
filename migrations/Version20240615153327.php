<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240615153327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__detail_commande AS SELECT id, reference_id, id_commande_id, quantite, prixunitaire FROM detail_commande');
        $this->addSql('DROP TABLE detail_commande');
        $this->addSql('CREATE TABLE detail_commande (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference_id INTEGER NOT NULL, id_commande_id INTEGER NOT NULL, quantite NUMERIC(10, 0) NOT NULL, prixunitairevente NUMERIC(10, 2) NOT NULL, CONSTRAINT FK_98344FA61645DEA9 FOREIGN KEY (reference_id) REFERENCES produit (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_98344FA69AF8E3A3 FOREIGN KEY (id_commande_id) REFERENCES commande (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO detail_commande (id, reference_id, id_commande_id, quantite, prixunitairevente) SELECT id, reference_id, id_commande_id, quantite, prixunitaire FROM __temp__detail_commande');
        $this->addSql('DROP TABLE __temp__detail_commande');
        $this->addSql('CREATE INDEX IDX_98344FA69AF8E3A3 ON detail_commande (id_commande_id)');
        $this->addSql('CREATE INDEX IDX_98344FA61645DEA9 ON detail_commande (reference_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__detail_commande AS SELECT id, reference_id, id_commande_id, quantite, prixunitairevente FROM detail_commande');
        $this->addSql('DROP TABLE detail_commande');
        $this->addSql('CREATE TABLE detail_commande (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference_id INTEGER NOT NULL, id_commande_id INTEGER NOT NULL, quantite NUMERIC(10, 0) NOT NULL, prixunitaire NUMERIC(10, 2) NOT NULL, CONSTRAINT FK_98344FA61645DEA9 FOREIGN KEY (reference_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_98344FA69AF8E3A3 FOREIGN KEY (id_commande_id) REFERENCES commande (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO detail_commande (id, reference_id, id_commande_id, quantite, prixunitaire) SELECT id, reference_id, id_commande_id, quantite, prixunitairevente FROM __temp__detail_commande');
        $this->addSql('DROP TABLE __temp__detail_commande');
        $this->addSql('CREATE INDEX IDX_98344FA61645DEA9 ON detail_commande (reference_id)');
        $this->addSql('CREATE INDEX IDX_98344FA69AF8E3A3 ON detail_commande (id_commande_id)');
    }
}

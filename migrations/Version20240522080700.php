<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240522080700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__achat AS SELECT id, dateachat, datefacture, numfacture, created_at FROM achat');
        $this->addSql('DROP TABLE achat');
        $this->addSql('CREATE TABLE achat (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, dateachat DATETIME NOT NULL, datefacture DATETIME NOT NULL, numfacture NUMERIC(10, 0) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('INSERT INTO achat (id, dateachat, datefacture, numfacture, created_at) SELECT id, dateachat, datefacture, numfacture, created_at FROM __temp__achat');
        $this->addSql('DROP TABLE __temp__achat');
        $this->addSql('CREATE TEMPORARY TABLE __temp__commande AS SELECT id, id_ville_id, id_client_id, datecommande, datelivraison, addresselivraison, etatcommande, created_at FROM commande');
        $this->addSql('DROP TABLE commande');
        $this->addSql('CREATE TABLE commande (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_ville_id INTEGER NOT NULL, id_client_id INTEGER NOT NULL, datecommande DATETIME NOT NULL, datelivraison DATETIME NOT NULL, addresselivraison VARCHAR(255) NOT NULL, etatcommande BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_6EEAA67DF7E4ECA3 FOREIGN KEY (id_ville_id) REFERENCES ville (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6EEAA67D99DED506 FOREIGN KEY (id_client_id) REFERENCES client (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO commande (id, id_ville_id, id_client_id, datecommande, datelivraison, addresselivraison, etatcommande, created_at) SELECT id, id_ville_id, id_client_id, datecommande, datelivraison, addresselivraison, etatcommande, created_at FROM __temp__commande');
        $this->addSql('DROP TABLE __temp__commande');
        $this->addSql('CREATE INDEX IDX_6EEAA67D99DED506 ON commande (id_client_id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67DF7E4ECA3 ON commande (id_ville_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__inventaire AS SELECT id, reference_id, update_at, note, stockinventaire, stockutiliser FROM inventaire');
        $this->addSql('DROP TABLE inventaire');
        $this->addSql('CREATE TABLE inventaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference_id INTEGER NOT NULL, update_at DATETIME NOT NULL, note VARCHAR(255) NOT NULL, stockinventaire NUMERIC(10, 0) NOT NULL, stockutiliser NUMERIC(10, 0) NOT NULL, CONSTRAINT FK_338920E01645DEA9 FOREIGN KEY (reference_id) REFERENCES produit (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO inventaire (id, reference_id, update_at, note, stockinventaire, stockutiliser) SELECT id, reference_id, update_at, note, stockinventaire, stockutiliser FROM __temp__inventaire');
        $this->addSql('DROP TABLE __temp__inventaire');
        $this->addSql('CREATE INDEX IDX_338920E01645DEA9 ON inventaire (reference_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__vente AS SELECT id, id_client_id, datevente, datefacture, numfacture, created_at FROM vente');
        $this->addSql('DROP TABLE vente');
        $this->addSql('CREATE TABLE vente (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_client_id INTEGER NOT NULL, datevente DATETIME NOT NULL, datefacture DATETIME NOT NULL, numfacture NUMERIC(10, 0) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_888A2A4C99DED506 FOREIGN KEY (id_client_id) REFERENCES client (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO vente (id, id_client_id, datevente, datefacture, numfacture, created_at) SELECT id, id_client_id, datevente, datefacture, numfacture, created_at FROM __temp__vente');
        $this->addSql('DROP TABLE __temp__vente');
        $this->addSql('CREATE INDEX IDX_888A2A4C99DED506 ON vente (id_client_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__achat AS SELECT id, dateachat, datefacture, numfacture, created_at FROM achat');
        $this->addSql('DROP TABLE achat');
        $this->addSql('CREATE TABLE achat (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, dateachat DATETIME NOT NULL, datefacture DATETIME NOT NULL, numfacture NUMERIC(10, 0) NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO achat (id, dateachat, datefacture, numfacture, created_at) SELECT id, dateachat, datefacture, numfacture, created_at FROM __temp__achat');
        $this->addSql('DROP TABLE __temp__achat');
        $this->addSql('CREATE TEMPORARY TABLE __temp__commande AS SELECT id, id_ville_id, id_client_id, datecommande, datelivraison, addresselivraison, etatcommande, created_at FROM commande');
        $this->addSql('DROP TABLE commande');
        $this->addSql('CREATE TABLE commande (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_ville_id INTEGER NOT NULL, id_client_id INTEGER NOT NULL, datecommande DATETIME NOT NULL, datelivraison DATETIME NOT NULL, addresselivraison VARCHAR(255) NOT NULL, etatcommande BOOLEAN NOT NULL, created_at DATETIME NOT NULL, CONSTRAINT FK_6EEAA67DF7E4ECA3 FOREIGN KEY (id_ville_id) REFERENCES ville (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6EEAA67D99DED506 FOREIGN KEY (id_client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO commande (id, id_ville_id, id_client_id, datecommande, datelivraison, addresselivraison, etatcommande, created_at) SELECT id, id_ville_id, id_client_id, datecommande, datelivraison, addresselivraison, etatcommande, created_at FROM __temp__commande');
        $this->addSql('DROP TABLE __temp__commande');
        $this->addSql('CREATE INDEX IDX_6EEAA67DF7E4ECA3 ON commande (id_ville_id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D99DED506 ON commande (id_client_id)');
        $this->addSql('ALTER TABLE inventaire ADD COLUMN ecart NUMERIC(10, 0) NOT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__vente AS SELECT id, id_client_id, datevente, datefacture, numfacture, created_at FROM vente');
        $this->addSql('DROP TABLE vente');
        $this->addSql('CREATE TABLE vente (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_client_id INTEGER NOT NULL, datevente DATETIME NOT NULL, datefacture DATETIME NOT NULL, numfacture NUMERIC(10, 0) NOT NULL, created_at DATETIME NOT NULL, CONSTRAINT FK_888A2A4C99DED506 FOREIGN KEY (id_client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO vente (id, id_client_id, datevente, datefacture, numfacture, created_at) SELECT id, id_client_id, datevente, datefacture, numfacture, created_at FROM __temp__vente');
        $this->addSql('DROP TABLE __temp__vente');
        $this->addSql('CREATE INDEX IDX_888A2A4C99DED506 ON vente (id_client_id)');
    }
}

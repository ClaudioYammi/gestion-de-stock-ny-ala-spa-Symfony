<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240520080110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE achat ADD COLUMN created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE commande ADD COLUMN created_at DATETIME NOT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, pseudo, created_at FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, pseudo VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('INSERT INTO user (id, email, roles, password, pseudo, created_at) SELECT id, email, roles, password, pseudo, created_at FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
        $this->addSql('ALTER TABLE vente ADD COLUMN created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__achat AS SELECT id, dateachat, datefacture, numfacture FROM achat');
        $this->addSql('DROP TABLE achat');
        $this->addSql('CREATE TABLE achat (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, dateachat DATETIME NOT NULL, datefacture DATETIME NOT NULL, numfacture NUMERIC(10, 0) NOT NULL)');
        $this->addSql('INSERT INTO achat (id, dateachat, datefacture, numfacture) SELECT id, dateachat, datefacture, numfacture FROM __temp__achat');
        $this->addSql('DROP TABLE __temp__achat');
        $this->addSql('CREATE TEMPORARY TABLE __temp__commande AS SELECT id, id_ville_id, id_client_id, datecommande, datelivraison, addresselivraison, etatcommande FROM commande');
        $this->addSql('DROP TABLE commande');
        $this->addSql('CREATE TABLE commande (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_ville_id INTEGER NOT NULL, id_client_id INTEGER NOT NULL, datecommande DATETIME NOT NULL, datelivraison DATETIME NOT NULL, addresselivraison VARCHAR(255) NOT NULL, etatcommande BOOLEAN NOT NULL, CONSTRAINT FK_6EEAA67DF7E4ECA3 FOREIGN KEY (id_ville_id) REFERENCES ville (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6EEAA67D99DED506 FOREIGN KEY (id_client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO commande (id, id_ville_id, id_client_id, datecommande, datelivraison, addresselivraison, etatcommande) SELECT id, id_ville_id, id_client_id, datecommande, datelivraison, addresselivraison, etatcommande FROM __temp__commande');
        $this->addSql('DROP TABLE __temp__commande');
        $this->addSql('CREATE INDEX IDX_6EEAA67DF7E4ECA3 ON commande (id_ville_id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D99DED506 ON commande (id_client_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, pseudo, created_at FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, pseudo VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password, pseudo, created_at) SELECT id, email, roles, password, pseudo, created_at FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__vente AS SELECT id, id_client_id, datevente, datefacture, numfacture FROM vente');
        $this->addSql('DROP TABLE vente');
        $this->addSql('CREATE TABLE vente (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_client_id INTEGER NOT NULL, datevente DATETIME NOT NULL, datefacture DATETIME NOT NULL, numfacture NUMERIC(10, 0) NOT NULL, CONSTRAINT FK_888A2A4C99DED506 FOREIGN KEY (id_client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO vente (id, id_client_id, datevente, datefacture, numfacture) SELECT id, id_client_id, datevente, datefacture, numfacture FROM __temp__vente');
        $this->addSql('DROP TABLE __temp__vente');
        $this->addSql('CREATE INDEX IDX_888A2A4C99DED506 ON vente (id_client_id)');
    }
}

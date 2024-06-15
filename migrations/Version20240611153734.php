<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240611153734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande ADD COLUMN numfacture NUMERIC(10, 0) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__commande AS SELECT id, id_ville_id, id_client_id, datecommande, tva, remise, etatcommande, created_at FROM commande');
        $this->addSql('DROP TABLE commande');
        $this->addSql('CREATE TABLE commande (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_ville_id INTEGER NOT NULL, id_client_id INTEGER NOT NULL, datecommande DATETIME NOT NULL, tva NUMERIC(10, 0) DEFAULT NULL, remise NUMERIC(10, 0) DEFAULT NULL, etatcommande BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_6EEAA67DF7E4ECA3 FOREIGN KEY (id_ville_id) REFERENCES ville (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6EEAA67D99DED506 FOREIGN KEY (id_client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO commande (id, id_ville_id, id_client_id, datecommande, tva, remise, etatcommande, created_at) SELECT id, id_ville_id, id_client_id, datecommande, tva, remise, etatcommande, created_at FROM __temp__commande');
        $this->addSql('DROP TABLE __temp__commande');
        $this->addSql('CREATE INDEX IDX_6EEAA67DF7E4ECA3 ON commande (id_ville_id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D99DED506 ON commande (id_client_id)');
    }
}

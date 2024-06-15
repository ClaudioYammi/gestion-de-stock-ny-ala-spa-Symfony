<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240610161358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__vente AS SELECT id, id_client_id, datevente, numfacture, created_at, tva, remise FROM vente');
        $this->addSql('DROP TABLE vente');
        $this->addSql('CREATE TABLE vente (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_client_id INTEGER NOT NULL, datevente DATETIME NOT NULL, numfacture NUMERIC(10, 0) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , tva NUMERIC(10, 0) DEFAULT NULL, remise NUMERIC(10, 0) DEFAULT NULL, CONSTRAINT FK_888A2A4C99DED506 FOREIGN KEY (id_client_id) REFERENCES client (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO vente (id, id_client_id, datevente, numfacture, created_at, tva, remise) SELECT id, id_client_id, datevente, numfacture, created_at, tva, remise FROM __temp__vente');
        $this->addSql('DROP TABLE __temp__vente');
        $this->addSql('CREATE INDEX IDX_888A2A4C99DED506 ON vente (id_client_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vente ADD COLUMN datefacture DATETIME NOT NULL');
    }
}

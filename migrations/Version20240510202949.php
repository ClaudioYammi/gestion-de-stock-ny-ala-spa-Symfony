<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240510202949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__client AS SELECT id, nom, adresse, prenom, email, telephone FROM client');
        $this->addSql('DROP TABLE client');
        $this->addSql('CREATE TABLE client (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, adresse VARCHAR(100) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, telephone NUMERIC(10, 0) NOT NULL)');
        $this->addSql('INSERT INTO client (id, nom, adresse, prenom, email, telephone) SELECT id, nom, adresse, prenom, email, telephone FROM __temp__client');
        $this->addSql('DROP TABLE __temp__client');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD COLUMN tel INTEGER NOT NULL');
    }
}

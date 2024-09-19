<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919004235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Detect if we are using PostgreSQL
        if ($this->connection->getDatabasePlatform()->getName() === 'postgresql') {
            $this->addSql('CREATE TABLE category (
                id SERIAL PRIMARY KEY, 
                name VARCHAR(128) NOT NULL, 
                description TEXT DEFAULT NULL
            )');
            $this->addSql('CREATE TABLE product (
                id SERIAL PRIMARY KEY, 
                category_id INTEGER DEFAULT NULL, 
                name VARCHAR(128) NOT NULL, 
                description TEXT DEFAULT NULL, 
                price DOUBLE PRECISION NOT NULL, 
                CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE
            )');
            $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
        }
        // Detect if we are using SQLite
        elseif ($this->connection->getDatabasePlatform()->getName() === 'sqlite') {
            $this->addSql('CREATE TABLE category (
                id INTEGER PRIMARY KEY AUTOINCREMENT, 
                name VARCHAR(128) NOT NULL, 
                description TEXT DEFAULT NULL
            )');
            $this->addSql('CREATE TABLE product (
                id INTEGER PRIMARY KEY AUTOINCREMENT, 
                category_id INTEGER DEFAULT NULL, 
                name VARCHAR(128) NOT NULL, 
                description TEXT DEFAULT NULL, 
                price DOUBLE PRECISION NOT NULL, 
                CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE
            )');
            $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE product');
    }
}

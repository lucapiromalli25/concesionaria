<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250804224015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehiculos ADD supplier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehiculos ADD CONSTRAINT FK_82CE64A72ADD6D8C FOREIGN KEY (supplier_id) REFERENCES proveedores (id)');
        $this->addSql('CREATE INDEX IDX_82CE64A72ADD6D8C ON vehiculos (supplier_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehiculos DROP FOREIGN KEY FK_82CE64A72ADD6D8C');
        $this->addSql('DROP INDEX IDX_82CE64A72ADD6D8C ON vehiculos');
        $this->addSql('ALTER TABLE vehiculos DROP supplier_id');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250804230753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cuotas (id INT AUTO_INCREMENT NOT NULL, venta_id INT NOT NULL, installment_number INT NOT NULL, amount NUMERIC(12, 2) NOT NULL, due_date DATE NOT NULL, status VARCHAR(50) NOT NULL, payment_date DATETIME DEFAULT NULL, receipt_name VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8BC7EE51F2A5805D (venta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cuotas ADD CONSTRAINT FK_8BC7EE51F2A5805D FOREIGN KEY (venta_id) REFERENCES ventas (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cuotas DROP FOREIGN KEY FK_8BC7EE51F2A5805D');
        $this->addSql('DROP TABLE cuotas');
    }
}

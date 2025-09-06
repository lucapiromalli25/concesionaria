<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250830145509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cuotas ADD receipt_number VARCHAR(50) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8BC7EE51B0ADB74C ON cuotas (receipt_number)');
        $this->addSql('ALTER TABLE ventas ADD receipt_number VARCHAR(50) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_808D9EB0ADB74C ON ventas (receipt_number)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8BC7EE51B0ADB74C ON cuotas');
        $this->addSql('ALTER TABLE cuotas DROP receipt_number');
        $this->addSql('DROP INDEX UNIQ_808D9EB0ADB74C ON ventas');
        $this->addSql('ALTER TABLE ventas DROP receipt_number');
    }
}

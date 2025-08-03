<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250802232812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ventas DROP INDEX IDX_808D9E25F7D575, ADD UNIQUE INDEX UNIQ_808D9E25F7D575 (vehiculo_id)');
        $this->addSql('ALTER TABLE ventas CHANGE vehiculo_id vehiculo_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ventas DROP INDEX UNIQ_808D9E25F7D575, ADD INDEX IDX_808D9E25F7D575 (vehiculo_id)');
        $this->addSql('ALTER TABLE ventas CHANGE vehiculo_id vehiculo_id INT DEFAULT NULL');
    }
}

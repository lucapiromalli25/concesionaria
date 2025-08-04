<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250803223645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_82CE64A79C6E762D ON vehiculos (chassis_number)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_82CE64A7FCFF3785 ON vehiculos (plate_number)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_82CE64A79C6E762D ON vehiculos');
        $this->addSql('DROP INDEX UNIQ_82CE64A7FCFF3785 ON vehiculos');
    }
}

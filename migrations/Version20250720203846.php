<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720203846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE marcas ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE marcas ADD CONSTRAINT FK_9FB784D4B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE marcas ADD CONSTRAINT FK_9FB784D4896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9FB784D4B03A8386 ON marcas (created_by_id)');
        $this->addSql('CREATE INDEX IDX_9FB784D4896DBBDE ON marcas (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE marcas DROP FOREIGN KEY FK_9FB784D4B03A8386');
        $this->addSql('ALTER TABLE marcas DROP FOREIGN KEY FK_9FB784D4896DBBDE');
        $this->addSql('DROP INDEX IDX_9FB784D4B03A8386 ON marcas');
        $this->addSql('DROP INDEX IDX_9FB784D4896DBBDE ON marcas');
        $this->addSql('ALTER TABLE marcas DROP created_by_id, DROP updated_by_id');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250802220026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE imagenes_vehiculos DROP FOREIGN KEY FK_501C088FB03A8386');
        $this->addSql('ALTER TABLE imagenes_vehiculos DROP FOREIGN KEY FK_501C088F896DBBDE');
        $this->addSql('DROP INDEX IDX_501C088FB03A8386 ON imagenes_vehiculos');
        $this->addSql('DROP INDEX IDX_501C088F896DBBDE ON imagenes_vehiculos');
        $this->addSql('ALTER TABLE imagenes_vehiculos DROP created_by_id, DROP updated_by_id, DROP created_at, DROP deleted_at, CHANGE vehiculo_id vehiculo_id INT NOT NULL, CHANGE file_path image_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE imagenes_vehiculos ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE vehiculo_id vehiculo_id INT DEFAULT NULL, CHANGE image_name file_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE imagenes_vehiculos ADD CONSTRAINT FK_501C088FB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE imagenes_vehiculos ADD CONSTRAINT FK_501C088F896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_501C088FB03A8386 ON imagenes_vehiculos (created_by_id)');
        $this->addSql('CREATE INDEX IDX_501C088F896DBBDE ON imagenes_vehiculos (updated_by_id)');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250802234553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservas (id INT AUTO_INCREMENT NOT NULL, vehiculo_id INT NOT NULL, cliente_id INT NOT NULL, vendedor_id INT NOT NULL, created_by_id INT DEFAULT NULL, updatede_by_id INT DEFAULT NULL, reservation_date DATETIME DEFAULT NULL, reservation_amount NUMERIC(20, 2) DEFAULT NULL, expiration_date DATETIME DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, observations LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_AA1DAB0125F7D575 (vehiculo_id), INDEX IDX_AA1DAB01DE734E51 (cliente_id), INDEX IDX_AA1DAB018361A8B8 (vendedor_id), INDEX IDX_AA1DAB01B03A8386 (created_by_id), INDEX IDX_AA1DAB014AE987D5 (updatede_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB0125F7D575 FOREIGN KEY (vehiculo_id) REFERENCES vehiculos (id)');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB01DE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes (id)');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB018361A8B8 FOREIGN KEY (vendedor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB01B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB014AE987D5 FOREIGN KEY (updatede_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB0125F7D575');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB01DE734E51');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB018361A8B8');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB01B03A8386');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB014AE987D5');
        $this->addSql('DROP TABLE reservas');
    }
}

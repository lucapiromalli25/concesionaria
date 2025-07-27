<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720212851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clientes (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, document_number VARCHAR(11) NOT NULL, phone VARCHAR(50) DEFAULT NULL, addres LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_50FE07D7B03A8386 (created_by_id), INDEX IDX_50FE07D7896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE imagenes_vehiculos (id INT AUTO_INCREMENT NOT NULL, vehiculo_id_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, orden INT DEFAULT NULL, is_main TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_501C088FEBED394B (vehiculo_id_id), INDEX IDX_501C088FB03A8386 (created_by_id), INDEX IDX_501C088F896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE modelos (id INT AUTO_INCREMENT NOT NULL, marca_id_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8441FCC227ACDDAF (marca_id_id), INDEX IDX_8441FCC2B03A8386 (created_by_id), INDEX IDX_8441FCC2896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehiculos (id INT AUTO_INCREMENT NOT NULL, version_id_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, anio INT NOT NULL, chassis_number VARCHAR(100) NOT NULL, engine_number VARCHAR(100) NOT NULL, color VARCHAR(50) NOT NULL, kilometers BIGINT DEFAULT NULL, state VARCHAR(50) DEFAULT NULL, entry_date DATETIME DEFAULT NULL, purchase_price NUMERIC(20, 2) DEFAULT NULL, suggested_retail_price NUMERIC(20, 2) DEFAULT NULL, internal_observations LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_82CE64A778984A52 (version_id_id), INDEX IDX_82CE64A7B03A8386 (created_by_id), INDEX IDX_82CE64A7896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ventas (id INT AUTO_INCREMENT NOT NULL, vehiculo_id_id INT DEFAULT NULL, cliente_id_id INT DEFAULT NULL, vendedor_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, sale_date DATETIME DEFAULT NULL, final_sale_price NUMERIC(20, 2) DEFAULT NULL, payment_method VARCHAR(100) DEFAULT NULL, observations LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_808D9EEBED394B (vehiculo_id_id), INDEX IDX_808D9EACC9C364 (cliente_id_id), INDEX IDX_808D9E8361A8B8 (vendedor_id), INDEX IDX_808D9EB03A8386 (created_by_id), INDEX IDX_808D9E896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE versiones (id INT AUTO_INCREMENT NOT NULL, modelo_id_id INT DEFAULT NULL, creted_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, characteristics LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6F5215647EF8F306 (modelo_id_id), INDEX IDX_6F5215646E39655A (creted_by_id), INDEX IDX_6F521564896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE clientes ADD CONSTRAINT FK_50FE07D7B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE clientes ADD CONSTRAINT FK_50FE07D7896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE imagenes_vehiculos ADD CONSTRAINT FK_501C088FEBED394B FOREIGN KEY (vehiculo_id_id) REFERENCES vehiculos (id)');
        $this->addSql('ALTER TABLE imagenes_vehiculos ADD CONSTRAINT FK_501C088FB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE imagenes_vehiculos ADD CONSTRAINT FK_501C088F896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE modelos ADD CONSTRAINT FK_8441FCC227ACDDAF FOREIGN KEY (marca_id_id) REFERENCES marcas (id)');
        $this->addSql('ALTER TABLE modelos ADD CONSTRAINT FK_8441FCC2B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE modelos ADD CONSTRAINT FK_8441FCC2896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehiculos ADD CONSTRAINT FK_82CE64A778984A52 FOREIGN KEY (version_id_id) REFERENCES versiones (id)');
        $this->addSql('ALTER TABLE vehiculos ADD CONSTRAINT FK_82CE64A7B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehiculos ADD CONSTRAINT FK_82CE64A7896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9EEBED394B FOREIGN KEY (vehiculo_id_id) REFERENCES vehiculos (id)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9EACC9C364 FOREIGN KEY (cliente_id_id) REFERENCES clientes (id)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9E8361A8B8 FOREIGN KEY (vendedor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9EB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9E896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE versiones ADD CONSTRAINT FK_6F5215647EF8F306 FOREIGN KEY (modelo_id_id) REFERENCES modelos (id)');
        $this->addSql('ALTER TABLE versiones ADD CONSTRAINT FK_6F5215646E39655A FOREIGN KEY (creted_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE versiones ADD CONSTRAINT FK_6F521564896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clientes DROP FOREIGN KEY FK_50FE07D7B03A8386');
        $this->addSql('ALTER TABLE clientes DROP FOREIGN KEY FK_50FE07D7896DBBDE');
        $this->addSql('ALTER TABLE imagenes_vehiculos DROP FOREIGN KEY FK_501C088FEBED394B');
        $this->addSql('ALTER TABLE imagenes_vehiculos DROP FOREIGN KEY FK_501C088FB03A8386');
        $this->addSql('ALTER TABLE imagenes_vehiculos DROP FOREIGN KEY FK_501C088F896DBBDE');
        $this->addSql('ALTER TABLE modelos DROP FOREIGN KEY FK_8441FCC227ACDDAF');
        $this->addSql('ALTER TABLE modelos DROP FOREIGN KEY FK_8441FCC2B03A8386');
        $this->addSql('ALTER TABLE modelos DROP FOREIGN KEY FK_8441FCC2896DBBDE');
        $this->addSql('ALTER TABLE vehiculos DROP FOREIGN KEY FK_82CE64A778984A52');
        $this->addSql('ALTER TABLE vehiculos DROP FOREIGN KEY FK_82CE64A7B03A8386');
        $this->addSql('ALTER TABLE vehiculos DROP FOREIGN KEY FK_82CE64A7896DBBDE');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9EEBED394B');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9EACC9C364');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9E8361A8B8');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9EB03A8386');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9E896DBBDE');
        $this->addSql('ALTER TABLE versiones DROP FOREIGN KEY FK_6F5215647EF8F306');
        $this->addSql('ALTER TABLE versiones DROP FOREIGN KEY FK_6F5215646E39655A');
        $this->addSql('ALTER TABLE versiones DROP FOREIGN KEY FK_6F521564896DBBDE');
        $this->addSql('DROP TABLE clientes');
        $this->addSql('DROP TABLE imagenes_vehiculos');
        $this->addSql('DROP TABLE modelos');
        $this->addSql('DROP TABLE vehiculos');
        $this->addSql('DROP TABLE ventas');
        $this->addSql('DROP TABLE versiones');
    }
}

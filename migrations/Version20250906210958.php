<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250906210958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clientes (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, document_number VARCHAR(11) NOT NULL, phone VARCHAR(50) DEFAULT NULL, address LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_50FE07D728F2AE32 (document_number), INDEX IDX_50FE07D7B03A8386 (created_by_id), INDEX IDX_50FE07D7896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cuotas (id INT AUTO_INCREMENT NOT NULL, venta_id INT NOT NULL, installment_number INT NOT NULL, amount NUMERIC(12, 2) NOT NULL, due_date DATE NOT NULL, status VARCHAR(50) NOT NULL, payment_date DATETIME DEFAULT NULL, receipt_name VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', receipt_number VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_8BC7EE51B0ADB74C (receipt_number), INDEX IDX_8BC7EE51F2A5805D (venta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE imagenes_vehiculos (id INT AUTO_INCREMENT NOT NULL, vehiculo_id INT NOT NULL, image_name VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', orden INT DEFAULT NULL, is_main TINYINT(1) DEFAULT NULL, INDEX IDX_501C088F25F7D575 (vehiculo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE marcas (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_9FB784D45E237E06 (name), INDEX IDX_9FB784D4B03A8386 (created_by_id), INDEX IDX_9FB784D4896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE modelos (id INT AUTO_INCREMENT NOT NULL, marca_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8441FCC281EF0041 (marca_id), INDEX IDX_8441FCC2B03A8386 (created_by_id), INDEX IDX_8441FCC2896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proveedores (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, contact_person VARCHAR(255) DEFAULT NULL, document_number VARCHAR(20) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, address LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservas (id INT AUTO_INCREMENT NOT NULL, vehiculo_id INT NOT NULL, cliente_id INT NOT NULL, vendedor_id INT NOT NULL, created_by_id INT DEFAULT NULL, updatede_by_id INT DEFAULT NULL, reservation_date DATETIME DEFAULT NULL, reservation_amount NUMERIC(20, 2) DEFAULT NULL, expiration_date DATETIME DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, observations LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_AA1DAB0125F7D575 (vehiculo_id), INDEX IDX_AA1DAB01DE734E51 (cliente_id), INDEX IDX_AA1DAB018361A8B8 (vendedor_id), INDEX IDX_AA1DAB01B03A8386 (created_by_id), INDEX IDX_AA1DAB014AE987D5 (updatede_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, complete_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehiculos (id INT AUTO_INCREMENT NOT NULL, version_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, supplier_id INT DEFAULT NULL, anio INT NOT NULL, chassis_number VARCHAR(100) NOT NULL, engine_number VARCHAR(100) NOT NULL, color VARCHAR(50) NOT NULL, kilometers BIGINT DEFAULT NULL, state VARCHAR(50) DEFAULT NULL, entry_date DATETIME DEFAULT NULL, purchase_price NUMERIC(20, 2) DEFAULT NULL, suggested_retail_price NUMERIC(20, 2) DEFAULT NULL, internal_observations LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', plate_number VARCHAR(20) DEFAULT NULL, purchase_document_name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_82CE64A7FCFF3785 (plate_number), INDEX IDX_82CE64A74BBC2705 (version_id), INDEX IDX_82CE64A7B03A8386 (created_by_id), INDEX IDX_82CE64A7896DBBDE (updated_by_id), INDEX IDX_82CE64A72ADD6D8C (supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ventas (id INT AUTO_INCREMENT NOT NULL, vehiculo_id INT NOT NULL, cliente_id INT DEFAULT NULL, vendedor_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, sale_date DATETIME DEFAULT NULL, final_sale_price NUMERIC(20, 2) DEFAULT NULL, payment_method VARCHAR(100) DEFAULT NULL, observations LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', number_of_installments INT DEFAULT NULL, sale_document_name VARCHAR(255) DEFAULT NULL, receipt_number VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_808D9EB0ADB74C (receipt_number), UNIQUE INDEX UNIQ_808D9E25F7D575 (vehiculo_id), INDEX IDX_808D9EDE734E51 (cliente_id), INDEX IDX_808D9E8361A8B8 (vendedor_id), INDEX IDX_808D9EB03A8386 (created_by_id), INDEX IDX_808D9E896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE versiones (id INT AUTO_INCREMENT NOT NULL, modelo_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, characteristics LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6F521564C3A9576E (modelo_id), INDEX IDX_6F521564B03A8386 (created_by_id), INDEX IDX_6F521564896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE clientes ADD CONSTRAINT FK_50FE07D7B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE clientes ADD CONSTRAINT FK_50FE07D7896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cuotas ADD CONSTRAINT FK_8BC7EE51F2A5805D FOREIGN KEY (venta_id) REFERENCES ventas (id)');
        $this->addSql('ALTER TABLE imagenes_vehiculos ADD CONSTRAINT FK_501C088F25F7D575 FOREIGN KEY (vehiculo_id) REFERENCES vehiculos (id)');
        $this->addSql('ALTER TABLE marcas ADD CONSTRAINT FK_9FB784D4B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE marcas ADD CONSTRAINT FK_9FB784D4896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE modelos ADD CONSTRAINT FK_8441FCC281EF0041 FOREIGN KEY (marca_id) REFERENCES marcas (id)');
        $this->addSql('ALTER TABLE modelos ADD CONSTRAINT FK_8441FCC2B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE modelos ADD CONSTRAINT FK_8441FCC2896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB0125F7D575 FOREIGN KEY (vehiculo_id) REFERENCES vehiculos (id)');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB01DE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes (id)');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB018361A8B8 FOREIGN KEY (vendedor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB01B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB014AE987D5 FOREIGN KEY (updatede_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehiculos ADD CONSTRAINT FK_82CE64A74BBC2705 FOREIGN KEY (version_id) REFERENCES versiones (id)');
        $this->addSql('ALTER TABLE vehiculos ADD CONSTRAINT FK_82CE64A7B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehiculos ADD CONSTRAINT FK_82CE64A7896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehiculos ADD CONSTRAINT FK_82CE64A72ADD6D8C FOREIGN KEY (supplier_id) REFERENCES proveedores (id)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9E25F7D575 FOREIGN KEY (vehiculo_id) REFERENCES vehiculos (id)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9EDE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes (id)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9E8361A8B8 FOREIGN KEY (vendedor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9EB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9E896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE versiones ADD CONSTRAINT FK_6F521564C3A9576E FOREIGN KEY (modelo_id) REFERENCES modelos (id)');
        $this->addSql('ALTER TABLE versiones ADD CONSTRAINT FK_6F521564B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE versiones ADD CONSTRAINT FK_6F521564896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clientes DROP FOREIGN KEY FK_50FE07D7B03A8386');
        $this->addSql('ALTER TABLE clientes DROP FOREIGN KEY FK_50FE07D7896DBBDE');
        $this->addSql('ALTER TABLE cuotas DROP FOREIGN KEY FK_8BC7EE51F2A5805D');
        $this->addSql('ALTER TABLE imagenes_vehiculos DROP FOREIGN KEY FK_501C088F25F7D575');
        $this->addSql('ALTER TABLE marcas DROP FOREIGN KEY FK_9FB784D4B03A8386');
        $this->addSql('ALTER TABLE marcas DROP FOREIGN KEY FK_9FB784D4896DBBDE');
        $this->addSql('ALTER TABLE modelos DROP FOREIGN KEY FK_8441FCC281EF0041');
        $this->addSql('ALTER TABLE modelos DROP FOREIGN KEY FK_8441FCC2B03A8386');
        $this->addSql('ALTER TABLE modelos DROP FOREIGN KEY FK_8441FCC2896DBBDE');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB0125F7D575');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB01DE734E51');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB018361A8B8');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB01B03A8386');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB014AE987D5');
        $this->addSql('ALTER TABLE vehiculos DROP FOREIGN KEY FK_82CE64A74BBC2705');
        $this->addSql('ALTER TABLE vehiculos DROP FOREIGN KEY FK_82CE64A7B03A8386');
        $this->addSql('ALTER TABLE vehiculos DROP FOREIGN KEY FK_82CE64A7896DBBDE');
        $this->addSql('ALTER TABLE vehiculos DROP FOREIGN KEY FK_82CE64A72ADD6D8C');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9E25F7D575');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9EDE734E51');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9E8361A8B8');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9EB03A8386');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9E896DBBDE');
        $this->addSql('ALTER TABLE versiones DROP FOREIGN KEY FK_6F521564C3A9576E');
        $this->addSql('ALTER TABLE versiones DROP FOREIGN KEY FK_6F521564B03A8386');
        $this->addSql('ALTER TABLE versiones DROP FOREIGN KEY FK_6F521564896DBBDE');
        $this->addSql('DROP TABLE clientes');
        $this->addSql('DROP TABLE cuotas');
        $this->addSql('DROP TABLE imagenes_vehiculos');
        $this->addSql('DROP TABLE marcas');
        $this->addSql('DROP TABLE modelos');
        $this->addSql('DROP TABLE proveedores');
        $this->addSql('DROP TABLE reservas');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE vehiculos');
        $this->addSql('DROP TABLE ventas');
        $this->addSql('DROP TABLE versiones');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

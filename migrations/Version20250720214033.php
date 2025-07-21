<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720214033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clientes ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL, DROP name, DROP username, CHANGE addres address LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE imagenes_vehiculos DROP FOREIGN KEY FK_501C088FEBED394B');
        $this->addSql('DROP INDEX IDX_501C088FEBED394B ON imagenes_vehiculos');
        $this->addSql('ALTER TABLE imagenes_vehiculos CHANGE vehiculo_id_id vehiculo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE imagenes_vehiculos ADD CONSTRAINT FK_501C088F25F7D575 FOREIGN KEY (vehiculo_id) REFERENCES vehiculos (id)');
        $this->addSql('CREATE INDEX IDX_501C088F25F7D575 ON imagenes_vehiculos (vehiculo_id)');
        $this->addSql('ALTER TABLE modelos DROP FOREIGN KEY FK_8441FCC227ACDDAF');
        $this->addSql('DROP INDEX IDX_8441FCC227ACDDAF ON modelos');
        $this->addSql('ALTER TABLE modelos CHANGE marca_id_id marca_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE modelos ADD CONSTRAINT FK_8441FCC281EF0041 FOREIGN KEY (marca_id) REFERENCES marcas (id)');
        $this->addSql('CREATE INDEX IDX_8441FCC281EF0041 ON modelos (marca_id)');
        $this->addSql('ALTER TABLE vehiculos DROP FOREIGN KEY FK_82CE64A778984A52');
        $this->addSql('DROP INDEX IDX_82CE64A778984A52 ON vehiculos');
        $this->addSql('ALTER TABLE vehiculos CHANGE version_id_id version_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehiculos ADD CONSTRAINT FK_82CE64A74BBC2705 FOREIGN KEY (version_id) REFERENCES versiones (id)');
        $this->addSql('CREATE INDEX IDX_82CE64A74BBC2705 ON vehiculos (version_id)');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9EACC9C364');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9EEBED394B');
        $this->addSql('DROP INDEX IDX_808D9EACC9C364 ON ventas');
        $this->addSql('DROP INDEX IDX_808D9EEBED394B ON ventas');
        $this->addSql('ALTER TABLE ventas ADD vehiculo_id INT DEFAULT NULL, ADD cliente_id INT DEFAULT NULL, DROP vehiculo_id_id, DROP cliente_id_id');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9E25F7D575 FOREIGN KEY (vehiculo_id) REFERENCES vehiculos (id)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9EDE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes (id)');
        $this->addSql('CREATE INDEX IDX_808D9E25F7D575 ON ventas (vehiculo_id)');
        $this->addSql('CREATE INDEX IDX_808D9EDE734E51 ON ventas (cliente_id)');
        $this->addSql('ALTER TABLE versiones DROP FOREIGN KEY FK_6F5215647EF8F306');
        $this->addSql('ALTER TABLE versiones DROP FOREIGN KEY FK_6F5215646E39655A');
        $this->addSql('DROP INDEX IDX_6F5215646E39655A ON versiones');
        $this->addSql('DROP INDEX IDX_6F5215647EF8F306 ON versiones');
        $this->addSql('ALTER TABLE versiones ADD modelo_id INT DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, DROP modelo_id_id, DROP creted_by_id');
        $this->addSql('ALTER TABLE versiones ADD CONSTRAINT FK_6F521564C3A9576E FOREIGN KEY (modelo_id) REFERENCES modelos (id)');
        $this->addSql('ALTER TABLE versiones ADD CONSTRAINT FK_6F521564B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6F521564C3A9576E ON versiones (modelo_id)');
        $this->addSql('CREATE INDEX IDX_6F521564B03A8386 ON versiones (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9E25F7D575');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9EDE734E51');
        $this->addSql('DROP INDEX IDX_808D9E25F7D575 ON ventas');
        $this->addSql('DROP INDEX IDX_808D9EDE734E51 ON ventas');
        $this->addSql('ALTER TABLE ventas ADD vehiculo_id_id INT DEFAULT NULL, ADD cliente_id_id INT DEFAULT NULL, DROP vehiculo_id, DROP cliente_id');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9EACC9C364 FOREIGN KEY (cliente_id_id) REFERENCES clientes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9EEBED394B FOREIGN KEY (vehiculo_id_id) REFERENCES vehiculos (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_808D9EACC9C364 ON ventas (cliente_id_id)');
        $this->addSql('CREATE INDEX IDX_808D9EEBED394B ON ventas (vehiculo_id_id)');
        $this->addSql('ALTER TABLE versiones DROP FOREIGN KEY FK_6F521564C3A9576E');
        $this->addSql('ALTER TABLE versiones DROP FOREIGN KEY FK_6F521564B03A8386');
        $this->addSql('DROP INDEX IDX_6F521564C3A9576E ON versiones');
        $this->addSql('DROP INDEX IDX_6F521564B03A8386 ON versiones');
        $this->addSql('ALTER TABLE versiones ADD modelo_id_id INT DEFAULT NULL, ADD creted_by_id INT DEFAULT NULL, DROP modelo_id, DROP created_by_id');
        $this->addSql('ALTER TABLE versiones ADD CONSTRAINT FK_6F5215647EF8F306 FOREIGN KEY (modelo_id_id) REFERENCES modelos (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE versiones ADD CONSTRAINT FK_6F5215646E39655A FOREIGN KEY (creted_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_6F5215646E39655A ON versiones (creted_by_id)');
        $this->addSql('CREATE INDEX IDX_6F5215647EF8F306 ON versiones (modelo_id_id)');
        $this->addSql('ALTER TABLE vehiculos DROP FOREIGN KEY FK_82CE64A74BBC2705');
        $this->addSql('DROP INDEX IDX_82CE64A74BBC2705 ON vehiculos');
        $this->addSql('ALTER TABLE vehiculos CHANGE version_id version_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehiculos ADD CONSTRAINT FK_82CE64A778984A52 FOREIGN KEY (version_id_id) REFERENCES versiones (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_82CE64A778984A52 ON vehiculos (version_id_id)');
        $this->addSql('ALTER TABLE clientes ADD name VARCHAR(255) NOT NULL, ADD username VARCHAR(255) NOT NULL, DROP first_name, DROP last_name, CHANGE address addres LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE imagenes_vehiculos DROP FOREIGN KEY FK_501C088F25F7D575');
        $this->addSql('DROP INDEX IDX_501C088F25F7D575 ON imagenes_vehiculos');
        $this->addSql('ALTER TABLE imagenes_vehiculos CHANGE vehiculo_id vehiculo_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE imagenes_vehiculos ADD CONSTRAINT FK_501C088FEBED394B FOREIGN KEY (vehiculo_id_id) REFERENCES vehiculos (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_501C088FEBED394B ON imagenes_vehiculos (vehiculo_id_id)');
        $this->addSql('ALTER TABLE modelos DROP FOREIGN KEY FK_8441FCC281EF0041');
        $this->addSql('DROP INDEX IDX_8441FCC281EF0041 ON modelos');
        $this->addSql('ALTER TABLE modelos CHANGE marca_id marca_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE modelos ADD CONSTRAINT FK_8441FCC227ACDDAF FOREIGN KEY (marca_id_id) REFERENCES marcas (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8441FCC227ACDDAF ON modelos (marca_id_id)');
    }
}

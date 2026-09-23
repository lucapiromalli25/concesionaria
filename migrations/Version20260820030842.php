<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Roles y funcionalidades en tablas propias.
 *
 * Crea rol, funcionalidad y las dos pivotes, y migra los roles que hoy viven en
 * el JSON de user.roles. La columna vieja se conserva: se borra en una migracion
 * posterior, cuando el modulo de Administracion este andando.
 *
 * Mapeo: ROLE_ADMIN -> administrador, ROLE_MANAGER -> gerente,
 * ROLE_SALESPERSON -> vendedor. Se asigna solo el rol mas alto de cada usuario,
 * porque la jerarquia de security.yaml ya cubre los de abajo.
 */
final class Version20260820030842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Roles y funcionalidades en tablas propias, con auditoria y pivotes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE funcionalidad (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, clave VARCHAR(100) NOT NULL, modulo VARCHAR(50) NOT NULL, nombre VARCHAR(100) NOT NULL, descripcion VARCHAR(255) DEFAULT NULL, orden INT DEFAULT 0 NOT NULL, status VARCHAR(20) DEFAULT \'activo\' NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_A7D8679964E8588B (clave), INDEX IDX_A7D86799B03A8386 (created_by_id), INDEX IDX_A7D86799896DBBDE (updated_by_id), INDEX IDX_A7D86799C76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rol (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, codigo VARCHAR(50) NOT NULL, nombre VARCHAR(100) NOT NULL, descripcion VARCHAR(255) DEFAULT NULL, es_sistema TINYINT(1) DEFAULT 0 NOT NULL, status VARCHAR(20) DEFAULT \'activo\' NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_E553F3720332D99 (codigo), INDEX IDX_E553F37B03A8386 (created_by_id), INDEX IDX_E553F37896DBBDE (updated_by_id), INDEX IDX_E553F37C76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rol_funcionalidad (rol_id INT NOT NULL, funcionalidad_id INT NOT NULL, created_by_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1034F7C34BAB96C (rol_id), INDEX IDX_1034F7C33637E16E (funcionalidad_id), INDEX IDX_1034F7C3B03A8386 (created_by_id), PRIMARY KEY(rol_id, funcionalidad_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_rol (user_id INT NOT NULL, rol_id INT NOT NULL, created_by_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_72EDD1A4A76ED395 (user_id), INDEX IDX_72EDD1A44BAB96C (rol_id), INDEX IDX_72EDD1A4B03A8386 (created_by_id), PRIMARY KEY(user_id, rol_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE funcionalidad ADD CONSTRAINT FK_A7D86799B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE funcionalidad ADD CONSTRAINT FK_A7D86799896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE funcionalidad ADD CONSTRAINT FK_A7D86799C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rol ADD CONSTRAINT FK_E553F37B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rol ADD CONSTRAINT FK_E553F37896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rol ADD CONSTRAINT FK_E553F37C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rol_funcionalidad ADD CONSTRAINT FK_1034F7C34BAB96C FOREIGN KEY (rol_id) REFERENCES rol (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rol_funcionalidad ADD CONSTRAINT FK_1034F7C33637E16E FOREIGN KEY (funcionalidad_id) REFERENCES funcionalidad (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rol_funcionalidad ADD CONSTRAINT FK_1034F7C3B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE usuario_rol ADD CONSTRAINT FK_72EDD1A4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuario_rol ADD CONSTRAINT FK_72EDD1A44BAB96C FOREIGN KEY (rol_id) REFERENCES rol (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuario_rol ADD CONSTRAINT FK_72EDD1A4B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');

        // ---- Roles base ----
        $this->addSql("INSERT INTO rol (codigo, nombre, descripcion, es_sistema, status, created_at) VALUES
            ('administrador', 'Administrador', 'Acceso total al sistema. No se puede editar ni borrar.', 1, 'activo', NOW()),
            ('gerente', 'Gerente', 'Inventario, proveedores, reportes y todo lo comercial.', 0, 'activo', NOW()),
            ('vendedor', 'Vendedor', 'Clientes, reservas y ventas.', 0, 'activo', NOW())");

        // ---- Migracion de los roles que hoy viven en el JSON ----
        // Solo el rol mas alto: la jerarquia de security.yaml cubre los de abajo.
        $this->addSql("INSERT INTO usuario_rol (user_id, rol_id, created_at)
            SELECT u.id, r.id, NOW()
            FROM user u
            JOIN rol r ON r.codigo = 'administrador'
            WHERE JSON_CONTAINS(u.roles, '\"ROLE_ADMIN\"')");

        $this->addSql("INSERT INTO usuario_rol (user_id, rol_id, created_at)
            SELECT u.id, r.id, NOW()
            FROM user u
            JOIN rol r ON r.codigo = 'gerente'
            WHERE JSON_CONTAINS(u.roles, '\"ROLE_MANAGER\"')
              AND NOT JSON_CONTAINS(u.roles, '\"ROLE_ADMIN\"')");

        $this->addSql("INSERT INTO usuario_rol (user_id, rol_id, created_at)
            SELECT u.id, r.id, NOW()
            FROM user u
            JOIN rol r ON r.codigo = 'vendedor'
            WHERE JSON_CONTAINS(u.roles, '\"ROLE_SALESPERSON\"')
              AND NOT JSON_CONTAINS(u.roles, '\"ROLE_MANAGER\"')
              AND NOT JSON_CONTAINS(u.roles, '\"ROLE_ADMIN\"')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE funcionalidad DROP FOREIGN KEY FK_A7D86799B03A8386');
        $this->addSql('ALTER TABLE funcionalidad DROP FOREIGN KEY FK_A7D86799896DBBDE');
        $this->addSql('ALTER TABLE funcionalidad DROP FOREIGN KEY FK_A7D86799C76F1F52');
        $this->addSql('ALTER TABLE rol DROP FOREIGN KEY FK_E553F37B03A8386');
        $this->addSql('ALTER TABLE rol DROP FOREIGN KEY FK_E553F37896DBBDE');
        $this->addSql('ALTER TABLE rol DROP FOREIGN KEY FK_E553F37C76F1F52');
        $this->addSql('ALTER TABLE rol_funcionalidad DROP FOREIGN KEY FK_1034F7C34BAB96C');
        $this->addSql('ALTER TABLE rol_funcionalidad DROP FOREIGN KEY FK_1034F7C33637E16E');
        $this->addSql('ALTER TABLE rol_funcionalidad DROP FOREIGN KEY FK_1034F7C3B03A8386');
        $this->addSql('ALTER TABLE usuario_rol DROP FOREIGN KEY FK_72EDD1A4A76ED395');
        $this->addSql('ALTER TABLE usuario_rol DROP FOREIGN KEY FK_72EDD1A44BAB96C');
        $this->addSql('ALTER TABLE usuario_rol DROP FOREIGN KEY FK_72EDD1A4B03A8386');
        $this->addSql('DROP TABLE rol_funcionalidad');
        $this->addSql('DROP TABLE usuario_rol');
        $this->addSql('DROP TABLE funcionalidad');
        $this->addSql('DROP TABLE rol');
    }
}

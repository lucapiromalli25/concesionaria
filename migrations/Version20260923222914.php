<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Campos de auditoria en `user`, para poder dar de baja a un usuario sin borrarlo.
 *
 * El generador arrastraba ademas un indice unico en reservas.receipt_number, otro
 * en user.dni y un cambio de user.roles a JSON. Son desfasajes viejos entre la
 * entidad y la base, no tienen que ver con esta funcionalidad y se sacaron a
 * proposito: van en su propia migracion cuando se decidan.
 */
final class Version20260923222914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Auditoria y baja logica de usuarios';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE user
                ADD created_by_id INT DEFAULT NULL,
                ADD updated_by_id INT DEFAULT NULL,
                ADD deleted_by_id INT DEFAULT NULL,
                ADD status VARCHAR(20) DEFAULT 'activo' NOT NULL,
                ADD created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                ADD deleted_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);

        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8D93D649B03A8386 ON user (created_by_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649896DBBDE ON user (updated_by_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649C76F1F52 ON user (deleted_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B03A8386');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649896DBBDE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649C76F1F52');
        $this->addSql('DROP INDEX IDX_8D93D649B03A8386 ON user');
        $this->addSql('DROP INDEX IDX_8D93D649896DBBDE ON user');
        $this->addSql('DROP INDEX IDX_8D93D649C76F1F52 ON user');
        $this->addSql('ALTER TABLE user DROP created_by_id, DROP updated_by_id, DROP deleted_by_id, DROP status, DROP created_at, DROP updated_at, DROP deleted_at');
    }
}

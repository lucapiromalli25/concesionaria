<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Corrige la descripcion del rol administrador.
 *
 * Decia "Acceso total al sistema. No se puede editar ni borrar", que era cierto
 * cuando pasaba todos los permisos por codigo. Desde que existe el superadmin,
 * administrador tiene permisos explicitos y se edita desde la matriz como
 * cualquier otro rol.
 */
final class Version20260924001500 extends AbstractMigration
{
    private const DESCRIPCION = 'Todo lo operativo y comercial, mas eliminar vehiculos y ventas. No entra a Administracion.';

    private const DESCRIPCION_ANTERIOR = 'Acceso total al sistema. No se puede editar ni borrar.';

    public function getDescription(): string
    {
        return 'Descripcion del rol administrador';
    }

    public function up(Schema $schema): void
    {
        // updated_at a mano: el AuditoriaListener solo se entera de los cambios
        // que pasan por el ORM, no de un UPDATE directo.
        $this->addSql(
            'UPDATE rol SET descripcion = ?, updated_at = NOW() WHERE codigo = ?',
            [self::DESCRIPCION, 'administrador']
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'UPDATE rol SET descripcion = ? WHERE codigo = ?',
            [self::DESCRIPCION_ANTERIOR, 'administrador']
        );
    }
}

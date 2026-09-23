<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rol superadmin: el unico con acceso al modulo de Administracion.
 *
 * Hasta ahora `administrador` pasaba TODOS los permisos por codigo en el
 * PermisoVoter, por eso los 9 administradores de produccion no tienen ni una
 * fila en rol_funcionalidad. Al pasarle el bypass a superadmin, si no se les
 * dieran los permisos explicitos se quedarian sin absolutamente nada.
 *
 * El orden importa: primero se crea el superadmin y se lo asigna, despues se le
 * dan a administrador sus permisos. Asi en ningun momento queda el sistema sin
 * alguien que pueda administrarlo.
 */
final class Version20260923234500 extends AbstractMigration
{
    /** DNI de la cuenta que queda como superadmin. */
    private const DNI_SUPERADMIN = '42721245';

    /** Claves del modulo de Administracion: quedan solo para el superadmin. */
    private const CLAVES_ADMIN = [
        'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',
        'roles.ver', 'roles.administrar',
    ];

    public function getDescription(): string
    {
        return 'Rol superadmin y permisos explicitos para administrador';
    }

    public function up(Schema $schema): void
    {
        // INSERT IGNORE se apoya en el indice unico de rol.codigo: si la migracion
        // se corre dos veces, no duplica ni falla.
        $this->addSql(<<<'SQL'
            INSERT IGNORE INTO rol (codigo, nombre, descripcion, es_sistema, status, created_at)
            VALUES ('superadmin', 'Superadministrador',
                    'Acceso total. Unico rol que entra a Usuarios, Roles y Permisos.',
                    1, 'activo', NOW())
        SQL);

        $this->addSql(
            <<<'SQL'
                INSERT IGNORE INTO usuario_rol (user_id, rol_id, created_at)
                SELECT u.id, r.id, NOW()
                  FROM user u
                  JOIN rol r ON r.codigo = 'superadmin'
                 WHERE u.dni = ?
            SQL,
            [self::DNI_SUPERADMIN]
        );

        $claves = self::CLAVES_ADMIN;
        $marcadores = implode(',', array_fill(0, \count($claves), '?'));
        $this->addSql(
            <<<SQL
                INSERT IGNORE INTO rol_funcionalidad (rol_id, funcionalidad_id, created_at)
                SELECT r.id, f.id, NOW()
                  FROM rol r
                  JOIN funcionalidad f ON f.clave NOT IN ({$marcadores})
                 WHERE r.codigo = 'administrador'
                   AND f.status = 'activo'
                SQL,
            $claves
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM rol_funcionalidad WHERE rol_id = (SELECT id FROM rol WHERE codigo = 'superadmin')");
        $this->addSql("DELETE FROM usuario_rol WHERE rol_id = (SELECT id FROM rol WHERE codigo = 'superadmin')");
        $this->addSql("DELETE FROM rol WHERE codigo = 'superadmin'");
    }

    public function postUp(Schema $schema): void
    {
        // Si esto queda en cero, nadie entra a Administracion y hay que asignar el
        // rol a mano antes de que alguien se quede afuera.
        $asignados = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM usuario_rol ur JOIN rol r ON r.id = ur.rol_id WHERE r.codigo = 'superadmin'"
        );

        if ($asignados === 0) {
            $this->write(sprintf(
                '<error>ATENCION: no se encontro el usuario con DNI %s. El rol superadmin quedo sin asignar '
                .'y nadie puede entrar a Usuarios, Roles ni Permisos. Asignalo a mano antes de seguir.</error>',
                self::DNI_SUPERADMIN
            ));
        }
    }
}

<?php

namespace App\Service;

/**
 * Lee config/permisos.php. Es la fuente de verdad de que claves existen; la tabla
 * `funcionalidad` es su reflejo en base, para poder asignarlas desde el backoffice.
 */
class CatalogoPermisos
{
    /** @var array<string, array<string, array{nombre: string, descripcion?: string, roles: list<string>}>>|null */
    private ?array $catalogo = null;

    public function __construct(private readonly string $archivo)
    {
    }

    /** @return array<string, array<string, array{nombre: string, descripcion?: string, roles: list<string>}>> Modulo => clave => definicion. */
    public function porModulo(): array
    {
        return $this->catalogo ??= require $this->archivo;
    }

    /** @return list<string> */
    public function claves(): array
    {
        $claves = [];
        foreach ($this->porModulo() as $funcionalidades) {
            $claves = array_merge($claves, array_keys($funcionalidades));
        }

        return $claves;
    }

    public function existe(string $clave): bool
    {
        return \in_array($clave, $this->claves(), true);
    }
}

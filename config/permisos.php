<?php

/**
 * Catalogo de funcionalidades del sistema.
 *
 * Esta es la fuente de verdad: `php bin/console app:permisos:sincronizar` vuelca
 * estas claves a la tabla `funcionalidad`. Desde el backoffice se asignan a los
 * roles, pero no se crean ni se borran a mano.
 *
 * `roles` es la asignacion inicial: se aplica solo la primera vez que la clave se
 * inserta. Despues manda lo que este cargado en la base, asi el comando no pisa
 * los cambios hechos desde la UI. El rol superadmin no se lista porque pasa todos
 * los permisos por codigo (ver PermisoVoter); es el unico que llega al modulo de
 * Administracion, por eso esas claves no le tocan a administrador.
 *
 * Al agregar una pantalla nueva: se declara la clave aca, se corre el comando y
 * recien ahi se usa en el #[IsGranted] o en el is_granted() del template.
 */

return [
    'Panel' => [
        'dashboard.ver'          => ['nombre' => 'Ver el panel',                  'roles' => ['administrador', 'gerente', 'vendedor']],
        'dashboard.ver_totales'  => ['nombre' => 'Ver los totales de dinero',     'descripcion' => 'Facturacion, recaudado y por cobrar.', 'roles' => ['administrador', 'gerente']],
    ],

    'Vehiculos' => [
        'vehiculos.ver'                => ['nombre' => 'Ver el inventario',       'roles' => ['administrador', 'gerente', 'vendedor']],
        'vehiculos.crear'              => ['nombre' => 'Cargar vehiculos',        'roles' => ['administrador', 'gerente']],
        'vehiculos.editar'             => ['nombre' => 'Editar vehiculos',        'roles' => ['administrador', 'gerente']],
        'vehiculos.eliminar'           => ['nombre' => 'Eliminar vehiculos',      'roles' => ['administrador']],
        'vehiculos.ver_precio_compra'  => ['nombre' => 'Ver el precio de compra', 'descripcion' => 'Lo que se pago por el vehiculo, no el de venta.', 'roles' => ['administrador', 'gerente']],
        'vehiculos.ver_documentos'     => ['nombre' => 'Ver documentacion de compra', 'roles' => ['administrador', 'gerente']],
    ],

    'Reservas' => [
        'reservas.ver'        => ['nombre' => 'Ver reservas',              'roles' => ['administrador', 'gerente', 'vendedor']],
        'reservas.crear'      => ['nombre' => 'Registrar una reserva',     'roles' => ['administrador', 'gerente', 'vendedor']],
        'reservas.editar'     => ['nombre' => 'Editar reservas',           'roles' => ['administrador', 'gerente', 'vendedor']],
        'reservas.cancelar'   => ['nombre' => 'Cancelar una reserva',      'descripcion' => 'Devuelve el vehiculo a stock.', 'roles' => ['administrador', 'gerente']],
        'reservas.ver_recibo' => ['nombre' => 'Ver e imprimir el recibo',  'roles' => ['administrador', 'gerente', 'vendedor']],
    ],

    'Ventas' => [
        'ventas.ver'            => ['nombre' => 'Ver ventas',              'roles' => ['administrador', 'gerente', 'vendedor']],
        'ventas.crear'          => ['nombre' => 'Registrar una venta',     'roles' => ['administrador', 'gerente', 'vendedor']],
        'ventas.eliminar'       => ['nombre' => 'Eliminar una venta',      'descripcion' => 'Solo si no tiene cuotas pagas.', 'roles' => ['administrador']],
        'ventas.ver_recibo'     => ['nombre' => 'Ver e imprimir el recibo', 'roles' => ['administrador', 'gerente', 'vendedor']],
        'ventas.ver_documentos' => ['nombre' => 'Ver documentacion de la venta', 'roles' => ['administrador', 'gerente', 'vendedor']],
    ],

    'Cuotas' => [
        'cuotas.registrar_pago'  => ['nombre' => 'Registrar el pago de una cuota', 'roles' => ['administrador', 'gerente', 'vendedor']],
        'cuotas.anular_pago'     => ['nombre' => 'Anular un pago',                 'descripcion' => 'Borra el cobro y deja la cuota pendiente otra vez.', 'roles' => ['administrador', 'gerente']],
        'cuotas.ver_comprobante' => ['nombre' => 'Ver el comprobante de pago',     'roles' => ['administrador', 'gerente', 'vendedor']],
    ],

    'Planes de pago' => [
        'planes_pago.modificar' => ['nombre' => 'Regenerar el plan de cuotas', 'descripcion' => 'Borra las cuotas actuales y las arma de nuevo.', 'roles' => ['administrador', 'gerente']],
    ],

    'Clientes' => [
        'clientes.ver'    => ['nombre' => 'Ver clientes',   'roles' => ['administrador', 'gerente', 'vendedor']],
        'clientes.crear'  => ['nombre' => 'Cargar clientes', 'roles' => ['administrador', 'gerente', 'vendedor']],
        'clientes.editar' => ['nombre' => 'Editar clientes', 'roles' => ['administrador', 'gerente', 'vendedor']],
    ],

    'Proveedores' => [
        'proveedores.ver'    => ['nombre' => 'Ver proveedores',   'roles' => ['administrador', 'gerente']],
        'proveedores.crear'  => ['nombre' => 'Cargar proveedores', 'roles' => ['administrador', 'gerente']],
        'proveedores.editar' => ['nombre' => 'Editar proveedores', 'roles' => ['administrador', 'gerente']],
    ],

    'Catalogo' => [
        'catalogo.ver'      => ['nombre' => 'Ver marcas, modelos y versiones',   'roles' => ['administrador', 'gerente', 'vendedor']],
        'catalogo.crear'    => ['nombre' => 'Cargar marcas, modelos y versiones', 'roles' => ['administrador', 'gerente']],
        'catalogo.editar'   => ['nombre' => 'Editar marcas, modelos y versiones', 'roles' => ['administrador', 'gerente']],
        'catalogo.eliminar' => ['nombre' => 'Eliminar del catalogo',              'roles' => ['administrador']],
    ],

    'Reportes' => [
        'reportes.ver'      => ['nombre' => 'Ver reportes',                 'roles' => ['administrador', 'gerente']],
        'reportes.exportar' => ['nombre' => 'Exportar el inventario a Excel', 'roles' => ['administrador', 'gerente']],
    ],

    'Usuarios' => [
        'usuarios.ver'    => ['nombre' => 'Ver usuarios',                'roles' => []],
        'usuarios.crear'  => ['nombre' => 'Crear usuarios',              'roles' => []],
        'usuarios.editar' => ['nombre' => 'Editar usuarios y su acceso', 'roles' => []],
        'usuarios.eliminar' => ['nombre' => 'Dar de baja y reactivar usuarios', 'descripcion' => 'La baja es logica: el usuario no puede entrar mas, pero su historial de ventas y reservas queda intacto.', 'roles' => []],
    ],

    'Roles' => [
        'roles.ver'        => ['nombre' => 'Ver roles y permisos',      'roles' => []],
        'roles.administrar' => ['nombre' => 'Crear roles y asignar permisos', 'descripcion' => 'Da acceso a la matriz de permisos.', 'roles' => []],
    ],
];

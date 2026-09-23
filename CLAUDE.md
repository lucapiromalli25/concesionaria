# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Comandos

Todo corre dentro de Docker (`DATABASE_URL` apunta al host `db`, que solo existe en la red de compose). Ejecutar los comandos de consola dentro del contenedor `php`:

```bash
docker compose up -d                  # levanta php-fpm, nginx (http://localhost:8085) y mysql 8
docker compose exec php php bin/console <cmd>
docker compose exec php composer install
```

Comandos habituales:

```bash
# Migraciones
docker compose exec php php bin/console make:migration
docker compose exec php php bin/console doctrine:migrations:migrate

# Cache / assets (asset-mapper, sin build de node)
docker compose exec php php bin/console cache:clear
docker compose exec php php bin/console importmap:install
docker compose exec php php bin/console asset-map:compile   # solo para prod

# Debug
docker compose exec php php bin/console debug:router
docker compose exec php php bin/console debug:container

# Tests (PHPUnit 12; el directorio tests/ solo tiene bootstrap.php, no hay suites todavia)
docker compose exec php php bin/phpunit
docker compose exec php php bin/phpunit --filter NombreDelTest
docker compose exec php php bin/phpunit tests/Ruta/AlTest.php
```

No hay linter ni analizador estatico configurado (no hay php-cs-fixer, phpstan ni psalm en composer.json).

## Arquitectura

Symfony 7.3 + PHP 8.2+ + Doctrine ORM 3 sobre MySQL 8. App monolitica server-rendered (Twig + Bootstrap "KaiAdmin"), sin API. Idioma del dominio: espanol.

### Modelo de dominio

Catalogo jerarquico: `Marcas` -> `Modelos` -> `Versiones` -> `Vehiculos`. Un vehiculo apunta a `Versiones`, no directamente a marca/modelo; cualquier listado que muestre la marca necesita join triple (`v.version` -> `ver.modelo` -> `mod.marca`), ver `VehiculosRepository::findPaginatedWithRelations()` y `countVehiclesByBrand()`.

`Vehiculos` es el centro del sistema y tiene relaciones OneToOne con `Reservas` y `Ventas` (un vehiculo se reserva y se vende una sola vez), OneToMany con `ImagenesVehiculos`, y ManyToOne con `Proveedores`.

`Ventas` -> OneToMany `Cuotas` (cascade persist/remove + orphanRemoval). `Clientes` y `User` (vendedor) cuelgan de reservas y ventas.

### Maquina de estados del vehiculo

`App\Enum\VehicleStatus` (En Stock / Reservado / Vendido / En Mantenimiento) se guarda como **string** en `Vehiculos::$state` (no es un enum de Doctrine), por lo que las comparaciones siempre usan `VehicleStatus::X->value`. Las transiciones estan escritas a mano en los controladores:

- `ReservaController::new` exige `En Stock` y pasa a `Reservado`; al editar y marcar la reserva como `Cancelada` vuelve a `En Stock`.
- `VentaController::new` acepta `En Stock` o `Reservado`, pasa a `Vendido` y marca la reserva previa como `Completada`, precargando cliente y precio desde la reserva.

Al agregar flujos nuevos hay que respetar estas mismas comprobaciones; no hay Workflow component ni Voters.

### Numeros de recibo (patron de doble flush)

Ventas y reservas necesitan el ID autogenerado para armar el numero de recibo, asi que hacen `flush()`, setean `RV-000123` / `RR-000123` y vuelven a hacer `flush()`. Ver `VentaController::new` y `ReservaController::new`.

### Cuotas y planes de pago

`PaymentPlanGenerator` genera el plan (monto y vencimientos mensuales). Se usa de dos formas:

- `GET /ventas/plan-preview` devuelve el plan en JSON para previsualizarlo en el form de venta.
- El form de venta manda el plan ya editado por el usuario en un campo oculto `installments_data` (JSON) que `VentaController::new` deserializa y persiste como `Cuotas`. `persistForVenta()` existe pero el flujo web usa el JSON.

`CuotaController` registra pagos con comprobante (Vich) y permite cancelarlos, limpiando `paidCurrency`/`paidAmount`.

### Multi-moneda (ARS/USD)

No hay conversion en el servidor al momento de guardar: se persisten ambos valores. `Vehiculos` guarda `purchase_price`/`purchasePriceUsd` y `suggested_retail_price`/`suggestedRetailPriceUsd`. `Ventas::$saleCurrency`, `Reservas::$reservationCurrency` y `Cuotas::$paidCurrency` guardan la moneda elegida (default `ARS`).

La cotizacion es un parametro fijo en `config/services.yaml` (`app.exchange_rate_usd_ars`), que `VehiculosController` pasa al template y el Stimulus controller `assets/controllers/price_converter_controller.js` usa para autocompletar el campo en ARS. Los agregados del dashboard (`CuotasRepository::sumPaidInstallmentsByCurrency()`, etc.) suman por moneda sin convertir.

### Generacion de PDFs y Excel

- Recibos: Dompdf renderizando templates de `templates/receipt/`. Ventas genera el PDF on-demand; reservas ademas lo **guarda en disco** en `%kernel.project_dir%/public/uploads/documents/reservations` (parametro `reservations_directory`) y `app_reservas_view_pdf` lo sirve inline, con fallback a la generacion dinamica si el archivo no existe.
- `knp-snappy` + `wkhtmltopdf` estan instalados en la imagen pero el codigo usa Dompdf.
- `ReportController` exporta el inventario a XLSX con PhpSpreadsheet.

### Archivos subidos (VichUploader)

Cuatro mappings en `config/packages/vich_uploader.yaml`, todos con `SmartUniqueNamer` y destino bajo `public/uploads/`: `vehicle_images`, `receipt_files` (comprobantes de cuota), `purchase_documents` (`Vehiculos`), `sale_documents` (`Ventas`). Entidades con upload llevan `#[Vich\Uploadable]` y un campo `updatedAt` para que Vich detecte cambios.

### Seguridad

Login por **DNI** (no email): el user provider mapea `App\Entity\User` con `property: dni`. Jerarquia de roles en `config/packages/security.yaml`: `ROLE_ADMIN` > `ROLE_MANAGER` > `ROLE_SALESPERSON` > `ROLE_USER`. `access_control` esta vacio; la autorizacion se hace con `#[IsGranted]` a nivel de clase en cada controlador (admin: usuarios, marcas, modelos, versiones; manager: vehiculos, proveedores, reportes; salesperson: clientes, ventas, reservas).

`PlanDePagosController` referencia un `ROLE_PRICE` que no existe en la jerarquia.

### Frontend

AssetMapper + importmap (sin webpack/node). Stimulus y Turbo estan disponibles via `importmap.php`, pero `templates/base.html.twig` carga jQuery, Bootstrap, Chart.js y select2 como assets estaticos desde `assets/js` y `assets/css` (theme KaiAdmin), no via importmap. Los formularios de vehiculos se abren en modal y responden JSON cuando la request es XHR (`VehiculosController::getSuccessJsonResponse` / `getFormErrors`).

## Convenciones y trampas

- **Naming inconsistente en entidades**: conviven propiedades snake_case (`chassis_number`, `suggested_retail_price`, `created_at`) y camelCase (`plateNumber`, `purchasePriceUsd`) en la misma entidad. Con `naming_strategy: underscore_number_aware` ambas mapean a columnas snake_case, pero en DQL hay que usar el nombre exacto de la propiedad PHP. Verificar siempre antes de escribir una query.
- Entidades en plural (`Vehiculos`, `Ventas`, `Clientes`) representan **una** fila.
- Los importes monetarios son `string` (decimal de Doctrine), no float; castear explicitamente al operar.
- Auditoria (`createdBy`/`createdAt`/`updatedBy`/`updatedAt`) se setea a mano en cada controlador, no hay listener ni trait.
- Solo hay 2 migraciones (2025-09) para un esquema mucho mas grande: el resto se aplico fuera de migraciones. Antes de asumir el estado de la DB, verificar contra la base real, no contra `migrations/`.
- Funciones DQL de MySQL (`YEAR`, `MONTH`, `DAY`, `DATE`) estan registradas via `beberlei/doctrineextensions`.
- La logica de negocio vive hoy en los controladores; `src/Service/` solo tiene `PaymentPlanGenerator`. Al agregar logica nueva, preferir servicios (ver `.claudecode.md`, que documenta las convenciones de arquitectura y el protocolo de trabajo por fases: auditar, proponer, ejecutar).

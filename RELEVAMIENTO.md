# Relevamiento del sistema - Concesionaria (Atilio Automotores)

Fecha: 2026-08-18
Fuentes: codigo en `src/`, `templates/`, `config/`, y dump de produccion `u543976741_concesionaria.sql` (Navicat, MariaDB 11.8, host prod, 18/08/2026).

---

## 0. Foto del negocio segun el dump

| Tabla | Filas | Notas |
|---|---|---|
| vehiculos | 330 | 186 Vendido, 138 En Stock, 6 Reservado |
| ventas | 189 | 151 ARS / 38 USD. Financiadas: 101, Efectivo 33, Transferencia 11, "Otro" 44 |
| cuotas | 684 | 433 Pendiente / 251 Pagada. **262 pendientes ya vencidas** |
| reservas | 43 | 36 Completada, 7 Activa (2 de ellas vencidas) |
| clientes | 173 | |
| proveedores | 268 | 92 duplicados por nombre, 32 nunca usados |
| marcas / modelos / versiones | 27 / 409 / 405 | catalogo inflado, ver 2.1 |
| imagenes_vehiculos | 7 | sobre 330 vehiculos |
| user | 12 | 9 con ROLE_ADMIN, 3 con roles vacios, 4 operan de verdad |

Ventana temporal: ventas desde 2024-02-28 (carga historica) hasta 2026-07-27. Vehiculos cargados desde 2025-09-10. Ultimo pago de cuota registrado: 2026-07-27.

Usuarios que realmente operan (por volumen): id 3 Oriana (254 vehiculos, 143 ventas, 107 clientes), id 10 Gimena, id 7 Nahuel, id 4 Mariela.

---

## 1. Relevamiento de roles

### 1.1 Que existe hoy

Jerarquia declarada en `config/packages/security.yaml`:

```
ROLE_ADMIN > ROLE_MANAGER > ROLE_SALESPERSON > ROLE_USER
```

`access_control` esta vacio. Toda la autorizacion se resuelve con `#[IsGranted]` a nivel de clase de controlador.

| Modulo | Ruta base | Rol exigido en el controlador | Que permite |
|---|---|---|---|
| Dashboard | `/`, `/home` | ROLE_USER | KPIs, graficos, feed, cotizacion dolar |
| Vehiculos | `/vehiculos` | ROLE_MANAGER | listar, alta, edicion, detalle (no hay baja) |
| Clientes | `/clientes` | ROLE_SALESPERSON | listar, alta, edicion (no hay detalle ni baja) |
| Proveedores | `/proveedores` | ROLE_MANAGER | listar, alta, edicion |
| Marcas / Modelos / Versiones | `/marcas` `/modelos` `/versiones` | ROLE_ADMIN | listar, alta, edicion |
| Ventas | `/ventas` | ROLE_SALESPERSON | listar, alta, detalle, recibo PDF, **borrar** (no hay edicion) |
| Reservas | `/reservas` | ROLE_SALESPERSON | listar, alta, edicion, recibo PDF |
| Cuotas | `/cuotas` | ROLE_USER | registrar pago, cancelar pago, recibo PDF |
| Plan de pagos | `/plan-de-pagos` | MANAGER o ADMIN o **ROLE_PRICE** | regenerar el plan de cuotas |
| Reportes | `/admin/reports` | ROLE_MANAGER | export XLSX de inventario |
| Usuarios | `/admin/users` | ROLE_ADMIN | listar, alta, edicion (no hay baja) |
| Registro | `/register` | **ninguno (publico)** | crear usuario y quedar logueado |
| Descarga doc | `/download/document/{filename}` | **ninguno (publico)** | descarga de `public/documents/` |

### 1.2 Rol por rol: que puede hacer en la practica

**ROLE_USER** (todo usuario logueado, incluido uno auto-registrado):
- Ve el dashboard completo: facturacion total, deuda pendiente, ranking de vendedores, ultimos ingresos.
- Entra a `/cuotas/{id}/register-payment` y `/cuotas/{id}/cancel-payment`: **puede marcar cuotas como pagadas y revertir pagos**.
- No ve ningun item del menu lateral (todo el bloque esta bajo ROLE_SALESPERSON), pero las URLs responden.

**ROLE_SALESPERSON**: lo anterior mas clientes, ventas y reservas. Problema: el menu le muestra Vehiculos, Proveedores, Marcas, Modelos y Versiones, que sus controladores le niegan con 403. Y no puede abrir el detalle del vehiculo (`ROLE_MANAGER`) que si puede vender.

**ROLE_MANAGER**: agrega vehiculos, proveedores, reportes y modificacion de planes de pago.

**ROLE_ADMIN**: agrega catalogo (marcas/modelos/versiones) y usuarios.

**ROLE_CREATE_USER**: solo se usa en `templates/base.html.twig:132` para mostrar el link de Usuarios. No esta en la jerarquia, no lo otorga `UserType`.

**ROLE_PRICE**: solo se usa en `PlanDePagosController:16`. No esta en la jerarquia. La intencion aparente (controlar quien ve/edita precios) no esta implementada: `VehiculosType` inyectaba `Security` en el constructor y **nunca lo usaba**, asi que todos los precios se muestran a cualquier ROLE_MANAGER.

**Bug encontrado y corregido (2026-08-19):** ese mismo controlador declaraba `#[IsGranted("is_granted('ROLE_MANAGER') or ...")]` pasando la expresion como **string**. Symfony lo interpreta como el nombre de un atributo, ningun voter lo concede, y la ruta respondia **403 a todos**, incluidos los admin: "Modificar plan de pagos" nunca funciono desde que se escribio. Ahora va envuelta en `new Expression(...)`.

### 1.3 Hallazgos de roles

1. **Los roles no separan nada en produccion.** 9 de 12 usuarios tienen ROLE_ADMIN. El modelo de permisos existe en el codigo pero no en los datos.
2. **`UserType` borraba roles al editar** (corregido el 2026-08-19). Solo ofrecia USER, SALESPERSON, MANAGER, ADMIN: al guardar un usuario que tenia ROLE_CREATE_USER o ROLE_PRICE, esos roles se perdian en silencio. Es la explicacion mas probable de los 3 usuarios con `roles = []` en el dump (Mariela id 4, Silvana id 11, luca-piromalli id 2), que igual siguen figurando como creadores de registros. Ahora el formulario lista los cinco roles reales con su descripcion y avisa que lo no tildado se quita.
3. **`/register` es publico.** Cualquiera con la URL crea un usuario, queda autenticado (`$security->login(...)`) y con ROLE_USER accede al dashboard con la facturacion y a registrar/cancelar pagos de cuotas.
4. **El menu no coincide con los permisos reales**: links visibles que dan 403, y modulos accesibles sin link (Reportes solo se llega desde el boton en el listado de vehiculos).
5. **Login por DNI sin indice unico.** El provider usa `property: dni` (`security.yaml:11`) pero la tabla `user` solo tiene UNIQUE en `email`. Dos usuarios con el mismo DNI rompen el login (resultado no unico). Ademas `dni` es NULL-able.
6. **No hay Voters ni permisos por registro.** Un vendedor puede editar/borrar ventas de otro vendedor.
7. **No hay baja de usuarios** (ni desactivacion). Un empleado que se va queda con acceso salvo que se le editen los roles a mano.

---

## 2. Relevamiento end-to-end

### 2.1 Alta de vehiculo

Flujo: `/vehiculos` (ROLE_MANAGER) -> boton "Agregar Vehiculo" -> modal AJAX con `VehiculosType` -> desde el mismo modal se pueden crear Version, Modelo, Marca y Proveedor anidados -> guarda con auditoria seteada a mano -> `window.location.reload()`.

Lo que funciona bien: el alta anidada de catalogo dentro del modal evita salir del flujo.

Problemas:

- **No hay busqueda ni filtro.** El listado pagina de a 25 en el servidor (`VehiculosController:24`) y encima inicializa DataTables con `"paging": false` (`templates/vehiculos/index.html.twig:337`). El buscador de DataTables solo filtra las 25 filas de la pagina actual. Con 330 vehiculos, encontrar uno por patente implica recorrer 14 paginas a ojo. `VehiculoFilterType` existe (marca + estado) pero **no se usa en ningun lado**.
- **Catalogo inflado y duplicado.** 409 modelos y 405 versiones para 330 vehiculos: 391 modelos tienen exactamente 1 version, 343 versiones no tienen nombre, 72 nombres de modelo se repiten dentro de la misma marca sumando 129 filas de mas (AMAROK, CRUZE, C3, SURAN, X6 35I x4...), y 79 versiones no las usa ningun vehiculo. Tres marcas (Alfa Romeo, Dodge, GILERA) tienen modelos cargados y ningun vehiculo. La jerarquia Marca -> Modelo -> Version se esta usando como texto libre: cada alta crea entradas nuevas en vez de reutilizar. Las pantallas nuevas del catalogo muestran estos conteos y marcan los repetidos con `x{n}`.
- **El estado es un campo editable del formulario** (`VehiculosType:69`). Se puede poner "Vendido" a mano sin venta, o sacar de "Reservado" un auto con reserva activa, salteando la maquina de estados. En el dump hay 12 vehiculos inconsistentes (ver 2.7).
- **No hay precios cargados, ni de costo ni de venta.** Corregido al revisar contra la base local (el conteo inicial solo miraba NULL y el campo se guarda en `0.00`):
  - Precio de venta sugerido: 290 vehiculos en `0.00` + 21 NULL = **311 de 330 sin precio en pesos**. En USD: 302 en `0.00` + 26 NULL = **328 de 330**.
  - **135 de los 138 vehiculos en stock no tienen ningun precio cargado.**
  - Costo de compra: 307 NULL en pesos, 325 NULL en USD.
  - El patron es parejo desde 2025-09 hasta hoy, o sea que nunca se cargaron; el precio real recien aparece al vender (`ventas.final_sale_price`, cargado en las 189 ventas).
  - Consecuencia: la columna "Precio Venta" del listado esta vacia, el KPI "valor de inventario" del dashboard da practicamente cero, y no hay margen calculable.
- **El conversor USD -> ARS nunca funciono.** `assets/controllers/price_converter_controller.js` es un controller de Stimulus, pero `templates/base.html.twig` no llama a `{{ importmap('app') }}` en ningun lado: AssetMapper y Stimulus jamas se cargaron en las pantallas viejas. Los `data-controller` del formulario de vehiculo no hacen nada.
- Otros huecos de datos: kilometraje NULL en 298/330, fecha de ingreso NULL en 95/330, proveedor NULL en 89/330.
- **Sin validaciones de dominio**: no hay constraints de formato de patente, rango de anio, ni unicidad de chasis/motor (solo `plate_number` es UNIQUE en la DB). Ninguna entidad tiene asserts de Symfony Validator.
- No hay baja ni archivado de vehiculos. Las columnas `deleted_at` existen en toda la base y **no se usan nunca** (330/330 NULL): el soft delete esta modelado y no implementado.
- El modulo de imagenes practicamente no se usa (7 imagenes / 330 vehiculos). `orden` e `is_main` existen en la entidad y en la DB pero ninguna pantalla los aprovecha.

### 2.2 Reserva

Flujo: listado de vehiculos -> "Reservar" (solo si `En Stock`) -> `ReservaType` -> status `Activa`, vehiculo pasa a `Reservado` -> flush -> numero `RR-000000` -> segundo flush -> se genera y **guarda en disco** el PDF en `public/uploads/documents/reservations/` -> pantalla de exito.

Problemas:

- **Las reservas no vencen solas.** El estado `Vencida` existe en el formulario de edicion (`ReservaType:60`) pero ningun codigo lo asigna: no hay comando de consola, ni cron, ni listener. En el dump hay 7 reservas Activa y **las 7 ya pasaron su fecha de vencimiento** (la mas vieja vencio el 08/12/2025; la mas nueva, el 15/08/2026). Esos vehiculos siguen bloqueados en `Reservado` y no se pueden vender ni reservar de nuevo sin intervencion manual.
- Volver a stock solo ocurre si alguien entra a editar la reserva y elige `Cancelada` (`ReservaController:80`).
- **Auditoria muerta**: `created_by` y `updatede_by` (con el typo incluido, tanto en la entidad como en la columna) son NULL en 41 de 43 reservas. Nadie los setea.
- Sin validacion de importes ni fechas: hay una reserva de 300,00 ARS y otra cuya fecha de reserva (31/07/2026) es posterior a su vencimiento (05/07/2026).
- 2 reservas quedaron sin `receipt_number`, y `viewStoredPdf` arma el nombre del archivo a partir de ese numero: para esas cae siempre al fallback de regeneracion.
- No hay ruta para eliminar una reserva.

### 2.3 Venta

Flujo: listado -> "Vender" (si `En Stock` o `Reservado`) -> si venia de reserva precarga cliente y precio -> `VentaType` -> si el metodo es `Financiado` se habilita el editor de cuotas en JS (previsualizacion contra `/ventas/plan-preview`, filas editables a mano) -> submit: vehiculo a `Vendido`, reserva previa a `Completada`, se persisten las cuotas leyendo el JSON del campo oculto `installments_data`, flush, numero `RV-000000`, segundo flush -> exito -> recibo PDF.

Problemas:

- **No existe edicion de venta.** Hay `new`, `show`, `receipt` y `delete`. Para corregir un precio, un cliente o una fecha hay que borrar y rehacer la venta, y `delete` esta bloqueado si ya hay alguna cuota pagada (`VentaController:171`). Con 251 cuotas pagadas, buena parte de las ventas es hoy inmodificable.
- **El plan de cuotas se arma en el navegador y el servidor lo persiste sin validar.** `VentaController:94-111` hace `json_decode` de `installments_data` y guarda `amount` y `dueDate` tal cual. No se valida que la suma de las cuotas coincida con el precio de venta, ni que los montos sean positivos, ni el formato de fecha, ni que la cantidad coincida con `numberOfInstallments`. Un JSON manipulado entra directo a la base.
- **Anticipo y permuta no estan modelados.** Las observaciones muestran el patron real del negocio: "CLIENTE ENTREGO FORD ECOSPORT ANIO 2013 DOMINIO MYT484 Y $12000 DOLARES EN EFECTIVO", "entrega 6.500.000 en efectivo y 2 pagares... y deja un vehiculo en parte de pago". Eso es texto libre: el sistema no sabe cuanto entro de anticipo, ni que el auto entregado deberia ingresar al stock. 44 ventas quedaron clasificadas como metodo "Otro" (23%) porque el catalogo de metodos no alcanza.
- **El saldo se calcula mal.** `Ventas::getTotalPaid()` suma `cuota.getAmount()` (el monto teorico), no `paidAmount` (lo efectivamente cobrado), y no distingue moneda: si una cuota en ARS se cobra en USD, o se cobra parcial, el saldo pendiente queda mal. Ademas `getPendingBalance()` resta contra `final_sale_price` sin considerar anticipos.
- Registrar un pago parcial marca la cuota como `Pagada` igual (`CuotaController:29`): no hay pago parcial real ni cuota "Parcialmente pagada".
- Borrar una venta devuelve el vehiculo a `En Stock` (`VentaController:185`) aunque hubiera venido de una reserva que quedo en `Completada`: la reserva no se reabre y el par queda inconsistente.
- El listado de ventas hace `findBy([], ...)` sin joins: 189 ventas por vehiculo -> version -> modelo -> marca, cliente y vendedor, todo lazy. N+1 clasico.
- No hay estados de venta (borrador / confirmada / anulada): una venta existe o no existe.

### 2.4 Cobranza de cuotas

Flujo: se entra por el detalle de la venta -> modal de pago -> `status = Pagada`, `paidAmount` por defecto igual al monto, `paidCurrency` por defecto la moneda de la venta, recibo `RC-0000-00000`, comprobante subido con Vich. Cancelar el pago revierte todo y borra el comprobante.

Problemas:

- **No hay pantalla de cobranzas.** Existe `templates/cuota/index.html.twig` pero `CuotaController` no tiene ruta `index`: es una plantilla huerfana. Hoy no hay ninguna vista de "cuotas que vencen esta semana" ni "morosos". Las 262 cuotas vencidas impagas del dump no aparecen en ningun lado salvo entrando venta por venta.
- El dashboard muestra "deuda pendiente" agregada, pero no permite bajar al detalle.
- Cualquier ROLE_USER puede registrar y cancelar pagos (ver 1.3).
- Sin interes por mora, sin recargo, sin refinanciacion. Regenerar el plan (`/plan-de-pagos/{id}/modificar`) borra todas las cuotas y las recrea en partes iguales con vencimiento el dia 1 de cada mes, y solo si no hay ningun pago hecho.
- Los comprobantes subidos casi no se usan: `receipt_name` esta cargado en 2 de 684 cuotas.

### 2.5 Dashboard y reportes

- **Los KPIs suman pesos y dolares en el mismo numero.** `VentasRepository::getTotalSalesValue()`, `getSalesByMonth()`, `findSalesCountAndAmountBySalesperson()` y `VehiculosRepository::sumInventoryValue()` hacen `SUM(final_sale_price)` / `SUM(suggested_retail_price)` sin agrupar por moneda, con 38 ventas en USD sobre 189. Los graficos de "ventas por mes", "ranking de vendedores" y "valor de inventario" son numeros sin significado. Solo `getNonFinancedSalesValueByCurrency()` y los metodos de `CuotasRepository` agrupan bien por moneda.
- El dashboard llama a `https://dolarapi.com/v1/dolares` en cada carga (`HomeController:148`), sin cache y sin timeout explicito. La cotizacion que trae **no se usa para nada mas**: los formularios convierten con un valor fijo (ver 3).
- El feed de actividad y los "ultimos ingresos" cargan entidades completas y navegan relaciones lazy en el template.
- Reportes: un solo export (inventario a XLSX) con `findAll()` sin joins sobre 330 vehiculos, cada uno resolviendo version/modelo/marca/proveedor por separado. No hay export de ventas, de cuotas, de deuda por cliente ni de comisiones.
- No hay reporte de margen porque no se carga el costo (ver 2.1).

### 2.6 Documentos y archivos

- Recibos de venta, de reserva y de cuota se generan con Dompdf a partir de 2 plantillas. `knp-snappy` y `wkhtmltopdf` estan instalados (en el Dockerfile tambien) y no se usan.
- El PDF de reserva ademas se guarda en disco; los de venta y cuota se regeneran en cada descarga. Criterio inconsistente.
- Todos los uploads (imagenes, boletos de compra y venta, comprobantes de pago) van bajo `public/uploads/...`, o sea que son **descargables sin sesion** si se conoce la URL. El namer aleatorio de Vich es la unica proteccion.
- `/download/document/{filename}` es publico y arma la ruta concatenando el parametro de la URL.

### 2.7 Integridad de datos observada en produccion

Vehiculos con estado inconsistente (12 casos):

- Con venta registrada pero estado `En Stock`: 64, 120, 133, 144, 152, 321.
- Con venta registrada pero estado `Reservado`: 178.
- Estado `Vendido` sin venta asociada: 18, 113, 150, 171.
- Con reserva activa pero estado `Vendido`: 160.

Otros:

- 262 de 433 cuotas pendientes ya vencieron, por $ 440.163.001 mas USD 4.696.901.
- Las 7 reservas activas estan vencidas.
- 24 nombres de proveedor cargados mas de una vez, que suman 92 filas repetidas. El caso extremo es **KARAM: 52 filas identicas** (todas sin CUIT ni telefono), cada una asignada a un solo vehiculo, con ids repartidos entre el 97 y el 268; es decir, una fila nueva por cada operacion a lo largo de un año. Le siguen FIAT LEDIAN (12) y ACCOR CONSTRUCTORA S.R.L (7, con el CUIT escrito de dos formas). 268 proveedores para 330 vehiculos.

  **Causa raiz** (verificada): la tabla `proveedores` no tiene mas indice que la primary key (ningun UNIQUE sobre `name` ni sobre `document_number`), la entidad no tiene `#[UniqueEntity]` y `ProveedoresType` no valida nada. A eso se sumaba que el alta se hacia desde el modal anidado del formulario de vehiculo con solo escribir un nombre, mientras que encontrar el proveedor existente implicaba buscarlo en un select2 de 268 opciones: crear era mas rapido que encontrar. El listado nuevo marca los repetidos con `x{n}` y el alta rapida (`/catalogo/proveedor-rapido`) reutiliza el proveedor cuando el nombre coincide exactamente, pero las variantes de escritura (KARAM vs KARAM AUTOMOTORES) siguen entrando: hace falta el indice unico y una unificacion de los existentes.
- 10 documentos de cliente cargados con puntos ("23.054.432") contra el resto sin puntos, con UNIQUE en la columna: el mismo DNI escrito de las dos formas entra dos veces. Hay un cliente duplicado por nombre (LUIS FERNANDO ORSOMARSO).
- 3 vehiculos sin patente, y 311 de 330 sin precio de venta cargado (ver 2.1).

### 2.8 Estado del proyecto

- **Migraciones desfasadas**: produccion tiene aplicada una sola migracion (`Version20250906210958`, del 06/09/2025). Todo lo posterior (patente, precios USD, moneda de venta y de reserva, numeros de recibo, `paid_currency`, `orden`/`is_main` en imagenes) se aplico a mano contra la base. La migracion `Version20250908204950` nunca corrio en prod.
- **Produccion es MariaDB 11.8**, el `docker-compose.yml` local levanta MySQL 8.0.
- Sin tests (`tests/` solo tiene `bootstrap.php`), sin PHPStan, sin CS fixer, sin CI.
- Codigo muerto: `VehiculoFilterType`, `templates/cuota/index.html.twig`, `templates/presupuestos/` (vacio), `PaymentPlanGenerator::persistForVenta()` solo lo usa la regeneracion de plan, la inyeccion de `Security` en `VehiculosType`, messenger configurado sin mensajes.
- Toda la logica de negocio vive en controladores; `src/Service/` tiene una sola clase.
- Auditoria (`createdBy`, `updatedAt`, etc.) se setea copiando y pegando en cada controlador; en reservas directamente no se setea.

---

## 3. Inventario de hardcodeos

| # | Que | Donde | Impacto | Propuesta |
|---|---|---|---|---|
| 1 | **Cotizacion USD/ARS = 1250,00** | `config/services.yaml:8`, usado en `VehiculosController:66,97` y `assets/controllers/price_converter_controller.js` | Los precios se convierten con un valor fijo que hay que tocar por deploy, mientras el dashboard ya trae la cotizacion real | Tabla/config editable por UI + guardar la cotizacion usada en cada operacion |
| 2 | URL `https://dolarapi.com/v1/dolares` | `HomeController:148` | Llamada externa en cada carga, sin cache ni timeout, sin parametro | Servicio dedicado + cache + `%env()%`, y usarla en el punto 1 |
| 3 | Estados de vehiculo como string | `VehicleStatus` se guarda como string; comparaciones en `templates/vehiculos/index.html.twig:72,73,90,91,92`, `show.html.twig` | Cualquier cambio de nomenclatura rompe vistas en silencio | Enum de Doctrine + helper de badge en Twig |
| 4 | Estados de cuota `'Pendiente'`/`'Pagada'` | `CuotaController:29,106`, `Cuotas.php:34`, `Ventas.php:277,291,344,361`, `PaymentPlanGenerator:39`, `CuotasRepository:54,76`, `templates/ventas/show`, `vehiculos/show` | 10+ ocurrencias sueltas | Enum `InstallmentStatus` |
| 5 | Estados de reserva `'Activa'`/`'Vencida'`/`'Cancelada'`/`'Completada'` | `ReservaController:50,80`, `VentaController:82`, `ReservaType:60`, `templates/reservas/index:42,44` | Idem, y `Vencida` nunca se asigna | Enum `ReservationStatus` + comando de vencimiento |
| 6 | Metodos de pago | `VentaType:51-54`, comparacion `'Financiado'` en `VentaController:97`, `VentasRepository:139` (literal dentro del DQL), `templates/ventas/new.html.twig:174,205,233`, `vehiculos/show:138` | 23% de las ventas cae en "Otro" porque la lista no alcanza | Enum + catalogo configurable |
| 7 | Monedas `ARS`/`USD` | `VentaType:37-40`, `ReservaType:35-38`, `CuotaPaymentType:27-30`, arrays `['ARS'=>0,'USD'=>0]` en `VentasRepository:145` y `CuotasRepository:59` | Agregar una moneda toca 6 archivos | Enum `Currency` |
| 8 | Prefijos y padding de recibos `RV-`/`RR-`/`RC-` | `VentaController:118`, `ReservaController:57`, `CuotaController:44` | Numeracion atada al ID (no reiniciable, no por sucursal/anio), duplicada en 3 lugares con paddings distintos (6, 6, 4+5) | Servicio de numeracion configurable |
| 9 | **Datos de la empresa** "Atilio Automotores" | `templates/receipt/receipt_template.html.twig:93,145`, `reserva_receipt_template.html.twig:25,61`, logo en `base.html.twig:52,160` | Recibos sin CUIT, direccion ni telefono; imposible cambiar de marca o usar el sistema en otra sucursal | Parametros de empresa en config/DB + partial de encabezado |
| 10 | Archivo `BOLETTO.docx` | `templates/vehiculos/index.html.twig:12` | Boton "Descargar Boleto" apunta a un archivo fijo en `public/documents/` | Plantilla de boleto gestionable |
| 11 | Numeros magicos de paginacion y tops | `VehiculosController:24` (25), `VehiculosRepository:89` (25), `VentasRepository:130` (5), `HomeController` (3 top marcas, 3 vendedores, 5 ingresos, 15 dias, 12 meses) | | Parametros con default |
| 12 | Lista de colores | `templates/vehiculos/_form_modal.html.twig:32-34` | datalist fijo de 12 colores | Catalogo o texto libre normalizado |
| 13 | `'currency' => 'USD'` en los campos en pesos | `VehiculosType:102,107` | Los campos "Precio de Compra" y "Precio Venta (Sugerido)" (que son ARS) muestran simbolo USD | Corregir a ARS / usar el enum de moneda |
| 14 | Roles como strings | menu `base.html.twig:88,132`, todos los `#[IsGranted]`, `UserType:33-36` | `ROLE_PRICE` y `ROLE_CREATE_USER` no estan en la jerarquia y `UserType` los borra al editar | Constantes/enum de roles + jerarquia completa + Voters |
| 15 | Rutas de upload dentro de `public/` | `config/packages/vich_uploader.yaml` (4 mappings), parametro `reservations_directory` en `services.yaml:7` | Boletos, comprobantes y documentacion accesibles sin sesion | Mover fuera de `public/` y servir por controlador con permisos |
| 16 | Assets por CDN | `base.html.twig:34` (select2), `vehiculos/index.html.twig:338` (i18n de DataTables) | Dependencia externa en runtime; si el CDN falla, la tabla queda en ingles o sin estilos | Servir localmente via AssetMapper |
| 17 | Credenciales y secretos | `.env` commiteado con `DATABASE_URL="mysql://user:password@db:3306/..."` y `APP_SECRET=` vacio | APP_SECRET vacio debilita firmas de sesion/CSRF | `.env.local` / secrets de Symfony |
| 18 | Motor de base | `docker-compose.yml` usa `mysql:8.0`; prod es MariaDB 11.8 | Diferencias de SQL y de `serverVersion` en el DSN | Igualar imagen local a MariaDB 11.8 |
| 19 | Config de PDF repetida (`Arial`, A4, portrait) | `VentaController:152-158`, `ReservaController:105-115` y `148-160`, `CuotaController:80-88` | 4 copias del mismo bloque | Servicio `PdfGenerator` |
| 20 | Vencimiento de cuotas siempre el dia 1 | `PaymentPlanGenerator:16` (`first day of +N month`) | No se puede pactar otro dia de vencimiento | Dia configurable por venta |

---

## 4. Riesgos de seguridad (resumen)

1. `/register` publico: alta de usuario + login automatico, con acceso al dashboard financiero y a registrar/cancelar pagos de cuotas.
2. `/cuotas/*` protegido solo con ROLE_USER: registrar y **cancelar** pagos es la operacion mas sensible del sistema y es la menos protegida.
3. `/download/document/{filename}` sin autenticacion, construyendo la ruta con el parametro de URL.
4. Uploads servidos directamente desde `public/`.
5. `APP_SECRET` vacio y credenciales de base en el `.env` versionado.
6. Login por DNI sin indice unico en esa columna.
7. Sin Voters: cualquier vendedor opera sobre registros de cualquier otro.

---

## 4.bis Estado de la migracion de front (en curso desde 2026-08-18)

Decision tomada: front nuevo con **Tailwind v4 + Stimulus + Turbo** (via `symfonycasts/tailwind-bundle` y AssetMapper), migrando pantalla por pantalla. Las pantallas todavia no migradas siguen con `base.html.twig` (KaiAdmin + jQuery) y no se tocan.

Convivencia: `assets/app.js` desactiva Turbo Drive (`Turbo.session.drive = false`) para que la navegacion entre diseños sea una carga normal y los plugins jQuery del theme viejo no se re-inicialicen. Los `<turbo-frame>` siguen activos dentro de las pantallas nuevas.

Paleta: grafito calido + acento cobre, con modo claro/oscuro/automatico (toggle en la barra superior, recordado en `localStorage`). Todo sale de tokens CSS en `assets/styles/app.css`: cambiar el acento es una linea. Los unicos colores saturados de una tabla son los estados (verde en stock, celeste reservado, gris vendido, rojo vencido). La patente se muestra como chapa con la franja azul del Mercosur, porque es el identificador con el que trabaja la concesionaria.

| Pantalla | Estado |
|---|---|
| Panel (dashboard) | Migrada |
| Inventario de vehiculos (listado) | Migrada |
| Alta / edicion de vehiculo | Migrada (pagina completa, sin modales anidados) |
| Detalle de vehiculo | Migrada |
| Reservas (listado, alta, edicion, confirmacion) | Migrada |
| Ventas (listado con filtros, alta, detalle, cobro de cuotas, confirmacion) | Migrada |
| Clientes (listado con busqueda + alta/edicion en dialogo) | Migrada |
| Proveedores (idem, con aviso de duplicados) | Migrada |
| Catalogo: marcas, modelos y versiones (con conteos de uso y marcado de duplicados) | Migrada |
| Usuarios | Migrada |
| Login | Migrada |

Con esto el theme viejo (KaiAdmin + jQuery + DataTables + select2) ya no se usa en ninguna pantalla viva.
Lo unico que sigue extendiendo `base.html.twig` es `templates/cuota/index.html.twig`, que es la plantilla
huerfana sin ruta detectada en 2.4. Se borraron los siete `_form_modal.html.twig` que quedaron sin uso.

Los selectores de version, proveedor y cliente ya no vuelcan cientos de `<option>` en el HTML: buscan contra el servidor (`/catalogo/buscar/*`) con el tipo de formulario `EntitySearchType` y el controller Stimulus `combobox`.

Archivos nuevos: `templates/layout.html.twig`, `templates/partials/{icons,table,format,chart}.html.twig`, `templates/form/tailwind_theme.html.twig`, `templates/vehiculos/form.html.twig`, `templates/reservas/_form.html.twig`, `src/Controller/CatalogoController.php`, `src/Form/Type/EntitySearchType.php`, y los controllers Stimulus `theme`, `sidebar`, `filter_form`, `combobox`, `quick_create`, `collection`, `zero_km`, `gallery`.

Correcciones de reporteria aplicadas al migrar el panel (los KPIs sumaban pesos con dolares): `VentasRepository::getTotalSalesValueByCurrency()`, `getSalesByMonth()` y `findSalesCountAndAmountBySalesperson()` ahora separan por moneda, y `VehiculosRepository::inventoryValue()` reemplaza a `sumInventoryValue()` informando ademas cuantos vehiculos quedan afuera por no tener precio.

Comando de build: `docker compose exec php php bin/console tailwind:build` (o `--watch` mientras se desarrolla).

## 4.ter Donde retomar (cierre del 2026-08-19)

**La fase de diseño esta terminada.** Todas las pantallas vivas usan el layout nuevo. Lo que sigue es la
fase de logica y seguridad, en este orden:

1. **Seguridad (bloqueante).** Cerrar `/register`; subir el rol de `/cuotas` (hoy cualquier logueado cobra
   y anula pagos); proteger `/download/document/{filename}`; sacar los uploads de `public/`; `APP_SECRET`
   y credenciales fuera del repo; indice unico en `user.dni`.
2. **Integridad.** Servicio de maquina de estados del vehiculo y sacar `state` del alta; script para los
   12 vehiculos inconsistentes; comando de vencimiento de reservas (las 7 activas estan vencidas);
   regularizar migraciones contra el esquema real de prod.
3. **Negocio.** Validar el plan de cuotas en el servidor; corregir `getTotalPaid`/`getPendingBalance` para
   usar lo realmente cobrado y su moneda; edicion de venta; modelar anticipo y permuta.
4. **Datos.** Indice unico en proveedores + comando de unificacion (con modo simulacion); deduplicar
   modelos repetidos; normalizar documentos de cliente (los 10 con puntos).
5. **Configuracion.** Enums de estados/monedas/metodos de pago; entidad `Setting` con panel para la
   cotizacion, datos de empresa y numeracion de recibos.

### Entorno local

- `docker compose up -d` y la app queda en http://localhost:8085
- Base local cargada con el dump de produccion (330 vehiculos, 189 ventas, 684 cuotas).
- Usuario de prueba solo local: DNI **99999999** / **test1234**.
- Al tocar templates o CSS: `docker compose exec php php bin/console tailwind:build` (o `--watch`).
- Si se toca configuracion: `docker compose exec php php bin/console cache:clear`.

### Pendiente de commitear

El trabajo esta en el working tree, sin commitear, sobre `main`.

## 5. Propuesta de plan de mejora (por fases)

**Fase 1 - Integridad y seguridad (bloqueantes)**
- Cerrar `/register`, subir el rol requerido en `/cuotas`, proteger `/download/document`.
- Completar la jerarquia de roles (ROLE_PRICE, ROLE_CREATE_USER) y arreglar `UserType` para que deje de borrar roles.
- Unicidad de DNI en `user`, `APP_SECRET`, credenciales fuera del repo.
- Centralizar la maquina de estados del vehiculo en un servicio y sacar `state` del formulario de alta.
- Script de correccion de los 12 vehiculos inconsistentes.
- Regularizar migraciones contra el esquema real de prod y alinear el motor de la base local.

**Fase 2 - Correcciones de negocio**
- Separar monedas en todos los KPIs y reportes.
- Corregir `getTotalPaid` / `getPendingBalance` para usar el monto y la moneda realmente cobrados.
- Validar el plan de cuotas en el servidor.
- Edicion de venta (con reglas segun si hay pagos) y reapertura coherente de reserva al anular.
- Comando de vencimiento de reservas y de marcado de cuotas vencidas.

**Fase 3 - Configuracion (eliminar hardcodeos)**
- Enums para estados, monedas y metodos de pago.
- Panel de configuracion: datos de empresa, cotizacion (con toma automatica desde dolarapi + override manual), numeracion de recibos, dia de vencimiento, paginacion.
- Servicio unico de PDF y de numeracion.

**Fase 4 - Usabilidad operativa**
- Busqueda y filtros server-side en el inventario (patente, marca, modelo, estado, rango de precio) reemplazando el DataTables sin paginado.
- Pantalla de cobranzas: vencimientos del dia/semana, morosos, con accion de cobro directa.
- Normalizacion del catalogo marca/modelo/version (autocompletado que reutilice en vez de crear) y deduplicacion de proveedores y clientes.
- Modelado de anticipo y permuta en la venta.
- Carga de costo del vehiculo para habilitar reportes de margen.

---

## 5. Roles y funcionalidades en base de datos (2026-08-20)

Reemplaza el `user.roles` en JSON por tablas propias. Decidido con el usuario:
granularidad **modulo + acciones especiales** (~39 claves), catalogo **definido en codigo y
sincronizado a la DB**, permisos **solo por rol** (sin excepciones por usuario), y **7 campos
de auditoria en todas las tablas nuevas** (status, created_by/at, updated_by/at, deleted_by/at).

### Paso 1 - HECHO

| Tabla | Contenido |
|---|---|
| `rol` | codigo, nombre, descripcion, es_sistema + 7 auditoria |
| `funcionalidad` | clave (`modulo.accion`), modulo, nombre, descripcion, orden + 7 auditoria |
| `rol_funcionalidad` | PK (rol_id, funcionalidad_id) + created_by/at |
| `usuario_rol` | PK (user_id, rol_id) + created_by/at |

- Migracion `Version20260820030842`: esquema + datos. Sembro 3 roles (administrador de sistema,
  gerente, vendedor) y migro los roles del JSON. Resultado: **10 administradores y 3 usuarios
  sin rol**, que es exactamente lo que habia. Reasignar desde la UI cuando este el paso 3.
- `User::getRoles()` ahora deriva de la tabla: cada rol asignado aporta `ROLE_<CODIGO>`.
  La jerarquia de `security.yaml` conecta los nuevos con los viejos
  (`ROLE_ADMINISTRADOR -> ROLE_ADMIN, ROLE_PRICE, ROLE_CREATE_USER`), asi los `#[IsGranted]`
  actuales siguen funcionando sin tocarse hasta el paso 2.
- La columna `user.roles` queda pero ya no controla nada. Se borra cuando termine el paso 3.
- `AuditoriaListener` (prePersist/preUpdate) completa los 7 campos solo (antes se hacia a mano
  en cada controlador). Detecta las entidades por `CreatableInterface` / `AuditableInterface`;
  las constantes van en la interfaz porque PHP no deja leer constantes de un trait.
- El ABM de usuarios ya edita los roles nuevos (`EntityType` sobre `Rol` + sincronizacion de la
  pivote en `UserController::sincronizarRoles`). Si no, la pantalla habria quedado editando una
  columna que no hace nada.

Verificado: con el usuario de prueba pasado a gerente, `/admin/users/` y `/marcas/` quedan
bloqueados y `/vehiculos/` sigue abierto. La autorizacion sale de la base.

**Deuda de migraciones**: la base local tenia el esquema pero solo 1 migracion registrada. Se
marco `Version20250908204950` como aplicada sin ejecutarla (`doctrine:migrations:version --add`).
**En produccion hay que hacer lo mismo antes de correr la migracion nueva.**

Queda fuera a proposito y sigue pendiente: indice unico en `user.dni` y en
`reservas.receipt_number` (los muestra `doctrine:schema:update --dump-sql`), retrofit de los
7 campos a las 15 tablas viejas, y el filtro de Doctrine para que el borrado logico se respete
en las queries (hoy un `deleted_at` seteado no oculta nada).

### Paso 2 - HECHO

- `config/permisos.php`: catalogo de 39 claves agrupadas en 12 modulos. Cada una declara su
  asignacion inicial a `gerente` / `vendedor`; el administrador no se lista porque pasa todo por
  codigo. **Es la fuente de verdad**: una clave que no esta aca no existe.
- `app:permisos:sincronizar` (con `--simular`) vuelca el archivo a la tabla. Inserta las nuevas
  aplicando su asignacion inicial, actualiza nombre/modulo/orden de las existentes, y las que
  desaparecen del archivo se marcan **inactivas, no se borran** (borrarlas perderia en silencio
  a que roles estaban asignadas). La asignacion a roles solo se toca al insertar, asi el comando
  no pisa lo que se cambie desde el backoffice.
- `PermisoVoter`: resuelve las claves `modulo.accion`. El rol administrador pasa siempre por
  codigo. Una clave que no esta en el catalogo **se rechaza y se loguea como error**: es un typo
  en un IsGranted, no un permiso denegado. Cachea las claves del usuario por request.
- Reemplazados los chequeos de rol en los 13 controladores, en 9 lugares de templates y en el
  menu lateral (cada item declara `permiso:` en vez de `role:`).
- Se aprovecho para tapar dos agujeros: `CuotaController` pedia `ROLE_USER` (cualquier logueado
  podia anular pagos) y el boton de exportar a Excel no tenia ningun chequeo.

Verificado con el usuario de prueba rotando por los 3 roles:

| Ruta | administrador | gerente | vendedor |
|---|---|---|---|
| `/vehiculos/` | 200 | 200 | 200 |
| `/vehiculos/new` | 200 | 200 | denegado |
| `/proveedores/` | 200 | 200 | denegado |
| `/marcas/` (ver) | 200 | 200 | 200 |
| `/marcas/new` | 200 | 200 | denegado |
| `/admin/users/` | 200 | denegado | denegado |
| export a Excel | 200 | 200 | denegado |

**Efecto secundario que hubo que resolver**: un usuario sin rol recibia un 403 pelado al entrar
(antes el panel pedia ROLE_USER, que tenian todos). Ahora `HomeController` chequea
`dashboard.ver` por codigo y muestra `home/sin_permisos.html.twig` explicando que falta asignar
un rol. Los 3 usuarios sin rol de la migracion caen ahi.

Script de prueba: `scratchpad/probar_permisos.ps1 -Rol <codigo>`.

### Paso 3 - HECHO

Modulo Administracion con tres pantallas, en `src/Controller/Admin/` y `templates/admin/`:

- **Usuarios** (`/admin/users/`): movido de `src/Controller/` a `Admin\`; las rutas no cambiaron.
- **Roles** (`/admin/roles/`): ABM en dialogo. Muestra cuantos permisos y cuantos usuarios tiene
  cada uno. El codigo se elige al crear y despues no se puede cambiar (cambiarlo dejaria sin
  acceso a todos los que ya lo tienen) y se normaliza a slug en un PRE_SUBMIT del form, antes de
  validar. Baja logica, bloqueada si el rol es de sistema o si algun usuario lo tiene asignado.
- **Permisos** (`/admin/permisos/`): la matriz funcionalidad x rol. Filas agrupadas por modulo,
  una columna por rol editable mas una del rol de sistema tildada y deshabilitada. Boton
  "todos / ninguno" por columna (`matriz_permisos_controller.js`) y un solo guardado. Un rol sin
  ninguna casilla no llega en el POST, y eso se interpreta como "sin permisos", no como
  "no lo toques".

Las funcionalidades no se crean ni se borran desde la UI: salen de `config/permisos.php`.

Verificado end to end: se creo el rol "Cajero de prueba" desde el formulario (codigo normalizado a
`cajero_de_turno`, `created_by` completado solo por el listener), se le saco y devolvio un permiso
a gerente desde la matriz, y se lo borro de forma logica (status inactivo, `deleted_by` y
`updated_by` completados solos). 24 rutas devuelven 200 con el rol administrador.

**Bug encontrado de paso**: ningun formulario de dialogo renderizaba los errores globales del
form, asi que un fallo de CSRF se veia como "hice click en guardar y no paso nada". Se agrego el
bloque de error a los 9 formularios de dialogo.

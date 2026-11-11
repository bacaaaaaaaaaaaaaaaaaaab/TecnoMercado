# REPORTE TÉCNICO — ETAPA 3

## Auditoría de relaciones Eloquent

**Proyecto:** TecnoMercado  
**Directorio:** `C:\Proyectos\TecnoMercadoLaravel`  
**Framework:** Laravel 13.39.0  
**ORM:** Eloquent  
**Base de datos:** MySQL — `tecnomercado_laravel`  
**Etapa:** 3 — Auditoría de relaciones Eloquent  
**Estado:** CERRADA

---

## 1. Objetivo

Revisar las relaciones definidas en los modelos Eloquent de TecnoMercado para comprobar que correspondan con la estructura real de la base de datos, que utilicen correctamente las claves primarias y foráneas, y que permitan consultar los datos relacionados de manera coherente.

La auditoría también buscó detectar inconsistencias entre los modelos, las tablas y las reglas de negocio establecidas para la plataforma.

## 2. Alcance

Se revisaron los 16 modelos del proyecto:

- Carrito
- Categoria
- DetallePedido
- Favorito
- HistorialEstadoPedido
- Inventario
- ItemCarrito
- Notificacion
- Pedido
- PedidoVendedor
- Producto
- Rol
- User
- Usuario
- UsuarioRol
- Vendedor

Se analizaron las relaciones `BelongsTo`, `HasMany`, `HasOne` y `BelongsToMany`, incluyendo sus claves, tablas intermedias y modelos personalizados para las tablas pivote.

## 3. Actividades realizadas

### 3.1 Relaciones entre usuarios, roles y vendedores

Se revisaron las relaciones:

- `Usuario::roles()` — `BelongsToMany`.
- `Rol::usuarios()` — `BelongsToMany`.
- `Usuario::vendedor()` — `HasOne`.
- `Vendedor::usuario()` — `BelongsTo`.

Se verificó el uso de la tabla intermedia `usuario_roles`, las claves personalizadas y el modelo pivote `UsuarioRol`.

**Resultado:** las relaciones y sus claves corresponden con la estructura de la base de datos.

### 3.2 Relaciones entre categorías, productos, inventarios y favoritos

Se revisaron:

- `Categoria::productos()`.
- `Producto::categoria()`.
- `Producto::vendedor()`.
- `Producto::inventario()`.
- `Inventario::producto()`.
- `Producto::usuariosQueLoMarcaronFavorito()`.
- `Usuario::productosFavoritos()`.
- `Vendedor::productos()`.

También se comprobó el uso del modelo pivote `Favorito` y el campo adicional `created_at`.

**Resultado:** las relaciones corresponden con las claves foráneas y restricciones de unicidad existentes.

### 3.3 Relaciones entre carritos, artículos y productos

Se revisaron:

- `Usuario::carritos()`.
- `Carrito::usuario()`.
- `Carrito::items()`.
- `ItemCarrito::carrito()`.
- `ItemCarrito::producto()`.
- `Producto::itemsCarrito()`.

**Resultado:** las relaciones están correctamente definidas para consultar carritos, artículos y productos relacionados.

**Observación pendiente:** la regla de negocio establece que cada comprador debe tener un único carrito permanente. Actualmente, `carritos.id_usuario` no tiene una restricción `UNIQUE` y `Usuario::carritos()` está definido como `HasMany`. Será necesario alinear la relación y la restricción de base de datos con esa regla en una etapa posterior.

### 3.4 Relaciones entre pedidos y vendedores

Se revisaron:

- `Usuario::pedidos()`.
- `Pedido::comprador()`.
- `Pedido::pedidosVendedor()`.
- `PedidoVendedor::pedido()`.
- `PedidoVendedor::vendedor()`.
- `Vendedor::pedidosVendedor()`.

**Resultado:** las relaciones permiten representar un pedido general asociado a un comprador y sus registros independientes por vendedor.

### 3.5 Relaciones entre detalles, historial y productos

Se revisaron:

- `PedidoVendedor::detalles()`.
- `PedidoVendedor::historialEstados()`.
- `DetallePedido::pedidoVendedor()`.
- `DetallePedido::producto()`.
- `HistorialEstadoPedido::pedidoVendedor()`.
- `HistorialEstadoPedido::usuarioResponsable()`.
- `Producto::detallesPedido()`.
- `Usuario::historialesEstadosResponsable()`.

**Resultado:** las relaciones permiten consultar los detalles de cada solicitud, los productos involucrados, el historial de estados y el usuario responsable de cada registro.

### 3.6 Relaciones de notificaciones

Se revisaron:

- `Usuario::notificaciones()`.
- `Notificacion::usuario()`.
- `Notificacion::pedidoVendedor()`.
- `PedidoVendedor::notificaciones()`.

**Resultado:** las relaciones permiten vincular las notificaciones con el usuario destinatario y, cuando corresponde, con la solicitud del vendedor relacionada.

## 4. Comprobaciones técnicas

Durante la auditoría se realizaron las siguientes comprobaciones:

1. Revisión de los archivos fuente de los 16 modelos.
2. Inventario de los métodos de relación definidos.
3. Verificación de los tipos de relación Eloquent.
4. Revisión de claves foráneas y claves personalizadas.
5. Comprobación de las tablas pivote `usuario_roles` y `favoritos`.
6. Verificación de relaciones inversas.
7. Inspección de las 15 tablas de negocio.
8. Comprobación de la construcción de objetos de relación.
9. Revisión de las consultas SQL generadas por las relaciones.
10. Pruebas de carga anticipada mediante `with()` (_eager loading_).
11. Verificación de la configuración efectiva del proveedor de autenticación.

El inventario global identificó **36 métodos de relación**:

| Tipo de relación | Cantidad |
| ---------------- | -------: |
| BelongsTo        |       13 |
| HasMany          |       16 |
| HasOne           |        2 |
| BelongsToMany    |        5 |
| **Total**        |   **36** |

## 5. Hallazgos y observaciones

### 5.1 Relaciones coherentes

Se comprobó que las relaciones revisadas utilizan las tablas y claves correspondientes. Las relaciones inversas también se encuentran definidas de forma coherente.

Los modelos pivote personalizados `UsuarioRol` y `Favorito` se utilizan en las relaciones correspondientes.

### 5.2 Inventarios

La columna `inventarios.id_producto` cuenta con restricción de unicidad, lo cual corresponde con la relación `Producto::inventario()` de tipo `HasOne`.

### 5.3 Vendedores

La columna `vendedores.id_usuario` cuenta con restricción de unicidad, lo cual corresponde con la relación `Usuario::vendedor()` de tipo `HasOne`.

### 5.4 Carritos

Se identificó una diferencia entre la regla de negocio y la estructura actual:

- Regla: un carrito permanente por comprador.
- Estructura actual: `carritos.id_usuario` no es único.
- Relación actual: `Usuario::carritos()` utiliza `HasMany`.

Este punto queda registrado como pendiente de corrección. No se modificó durante la auditoría.

### 5.5 Restricciones de eliminación

Se revisaron las reglas `CASCADE`, `RESTRICT` y `SET NULL` de las claves foráneas. En particular, las referencias de los detalles de pedido a los productos utilizan restricciones que respaldan la regla de no eliminar productos con compras asociadas.

### 5.6 Autenticación

Se comprobó que el proveedor Eloquent del guard `web` utiliza `App\Models\Usuario` como modelo de autenticación.

El modelo `User` permanece separado como modelo heredado asociado a la tabla `users` y a la infraestructura de passkeys. No se identificaron referencias a `User::class` en los modelos de negocio revisados.

## 6. Limitaciones de la auditoría

Las tablas de negocio se encontraban sin registros suficientes para realizar pruebas funcionales con información real. Por ello, las comprobaciones se concentraron en la construcción de relaciones, sus consultas SQL y la carga anticipada, sin alterar ni poblar la base de datos.

La auditoría no implementó servicios, operaciones de negocio, políticas de autorización ni cambios estructurales.

## 7. Pendientes identificados

| Pendiente                            | Acción futura                                                              |
| ------------------------------------ | -------------------------------------------------------------------------- |
| Un carrito por comprador             | Evaluar cambio a `HasOne` y restricción `UNIQUE` en `carritos.id_usuario`. |
| Coherencia entre reglas y relaciones | Revisar durante la implementación de operaciones de carrito.               |
| Datos iniciales de roles             | Revisar en la auditoría de usuarios y roles.                               |
| Seeder de usuarios                   | Revisar el uso de `App\Models\User` frente a `App\Models\Usuario`.         |

Estos pendientes no invalidan el cierre de la auditoría de relaciones, pero deberán resolverse en las etapas correspondientes.

## 8. Conclusión

La etapa 3 de TecnoMercado concluye satisfactoriamente. Se revisaron los 16 modelos y sus 36 métodos de relación, verificando su correspondencia con las tablas, claves y restricciones existentes.

Las relaciones principales entre usuarios, roles, vendedores, productos, categorías, inventarios, favoritos, carritos, pedidos, detalles, historiales y notificaciones presentan una estructura coherente.

Se documentó como principal discrepancia la relación de carritos, debido a que la regla de negocio establece un único carrito permanente por comprador, mientras que la estructura actual permite más de uno.

**Estado final: ETAPA 3 — CERRADA.**

La siguiente actividad corresponde a la **etapa 4: Auditoría de reglas de negocio**, comenzando por usuarios y roles. Esta etapa se realizará mediante revisión y documentación, sin implementar cambios hasta concluir la auditoría.

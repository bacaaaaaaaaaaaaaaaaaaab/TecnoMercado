# REPORTE TÉCNICO — ETAPA 4
## Auditoría de reglas de negocio
**Proyecto:** TecnoMercado  
**Ruta del proyecto:** C:\Proyectos\TecnoMercadoLaravel  
**Tecnologías:** Laravel 13.39.0, PHP 8.5.0, MySQL 8.4.11, Livewire 4.4.8 y Tailwind CSS 4  
**Base de datos auditada:** tecnomercado_laravel  
**Fecha:** 11 de noviembre de 2026  
**Etapa:** 4 de 12  
**Estado:** Auditoría concluida; informe técnico en elaboración.

---

# 1. Objetivo de la etapa

Realizar una auditoría de las reglas de negocio de TecnoMercado para determinar si la estructura actual de la base de datos, los modelos Eloquent, sus relaciones y el código existente representan correctamente el funcionamiento esperado de la plataforma.

La auditoría busca identificar:

- Reglas de negocio correctamente representadas.
- Funcionalidades que todavía no cuentan con implementación.
- Inconsistencias entre las reglas establecidas y la estructura actual.
- Riesgos de integridad y operación.
- Comportamientos que deberán implementarse o corregirse en las siguientes etapas.

Esta etapa tiene carácter exclusivamente de revisión. No contempla implementar funcionalidades ni modificar la base de datos.

# 2. Alcance y metodología

Se revisaron las siguientes áreas:

1. Usuarios y roles.
2. Vendedores.
3. Productos y categorías.
4. Inventarios.
5. Favoritos y carritos.
6. Pedidos y generación de solicitudes.
7. Respuestas de vendedores.
8. Notificaciones e historial.
9. Integridad global.

Para cada área se siguió esta metodología:

1. Identificar y documentar la regla de negocio.
2. Revisar su representación en las tablas y migraciones.
3. Examinar los modelos y relaciones Eloquent correspondientes.
4. Buscar operaciones y funcionalidades existentes en el código.
5. Identificar cumplimiento, cumplimiento parcial o ausencia de implementación.
6. Registrar riesgos, contradicciones y comportamientos esperados.
7. Definir los puntos que deberán atenderse durante la implementación.

# 3. Reglas generales del sistema

Las siguientes reglas constituyen la base del funcionamiento esperado de TecnoMercado:

- Una misma cuenta puede desempeñar los roles de comprador y vendedor.
- Todo usuario nuevo debe registrarse inicialmente como comprador.
- Un comprador puede convertirse en vendedor desde la configuración de su cuenta, agregando el rol de vendedor sin perder el rol de comprador.
- Cada comprador debe tener un único carrito permanente.
- El carrito puede contener productos de diferentes vendedores.
- Al solicitar una compra, el sistema debe generar un pedido general y solicitudes independientes para cada vendedor involucrado.
- Cada vendedor debe poder aceptar o rechazar su solicitud de manera independiente.
- Los precios registrados en los pedidos deben conservarse como referencias históricas.
- Las operaciones que generen pedidos, solicitudes y reservas deben ejecutarse mediante transacciones.
- TecnoMercado funciona como intermediario: no procesa pagos ni administra entregas o envíos.
- El vendedor y el comprador acuerdan directamente las condiciones de entrega.
- Un producto que tenga compras asociadas no debe poder eliminarse.

# 4. Resultados de las auditorías

## 4.1 Usuarios y roles

### Elementos revisados

- Modelo Usuario.
- Modelo Rol.
- Modelo UsuarioRol.
- Relaciones entre usuarios y roles.
- Acción de registro CreateNewUser.
- DatabaseSeeder.
- Rutas, middleware, políticas y controles de acceso.
- Configuración de autenticación.
- Interfaz de configuración del perfil.

### Hallazgos

1. El modelo Usuario tiene una relación muchos a muchos con Rol mediante la tabla usuario_roles.
2. La tabla pivote utiliza una clave primaria compuesta por id_usuario e id_rol, evitando asignaciones duplicadas del mismo rol.
3. El registro crea usuarios con el campo activo establecido en true.
4. La acción CreateNewUser no asigna automáticamente el rol comprador.
5. No se encontró una operación que permita convertir una cuenta de comprador a vendedor.
6. No se encontró una operación que agregue el rol vendedor conservando el rol comprador.
7. No se encontraron controles de acceso propios para diferenciar las operaciones de comprador, vendedor y administrador.
8. No se encontraron operaciones de activación y desactivación de cuentas.
9. La interfaz de configuración del perfil todavía no contiene la opción para convertirse en vendedor.
10. DatabaseSeeder utiliza el modelo User, mientras que la autenticación principal utiliza Usuario.
11. Los roles de negocio todavía no cuentan con registros iniciales en la base de datos.

### Matriz de cumplimiento

| Regla | Resultado |
|---|---|
| Relación usuario-roles | Conforme estructuralmente |
| Registro automático como comprador | Pendiente |
| Conversión a vendedor | Pendiente |
| Conservación de ambos roles | Parcial |
| Restricciones de administradores | Pendiente de definición |
| Activación de cuentas | Parcial |
| Desactivación de cuentas | Pendiente |
| Interfaz para administrar roles | Pendiente |

### Comportamiento esperado

Todo usuario nuevo debe registrarse como comprador. Desde la configuración de su cuenta podrá solicitar convertirse en vendedor. El sistema deberá agregar el rol vendedor y conservar el rol comprador.

Los tres administradores tendrán restricciones especiales. Sus reglas específicas deberán definirse antes de implementar los controles correspondientes.

### Conclusión

La estructura de relaciones permite representar múltiples roles por usuario, pero todavía no existe la lógica completa para asignarlos, administrarlos y restringir operaciones según el rol.

## 4.2 Vendedores

### Elementos revisados

- Modelo Vendedor.
- Migración de vendedores.
- Relación con Usuario.
- Relación con Producto.
- Relación con PedidoVendedor.
- Estados y operaciones relacionadas con perfiles comerciales.

### Hallazgos

1. La tabla vendedores tiene una clave foránea hacia usuarios.
2. El campo id_usuario tiene restricción UNIQUE, garantizando un solo perfil de vendedor por cuenta.
3. El modelo Vendedor tiene las relaciones correspondientes con Usuario, Producto y PedidoVendedor.
4. Se dispone de nombre comercial, descripción, teléfono de contacto y estado.
5. No se encontró una operación que cree perfiles de vendedor.
6. No se encontraron operaciones para editar o administrar perfiles comerciales.
7. El campo estado es VARCHAR(20), sin catálogo de valores permitidos ni comportamiento implementado.
8. No se encontraron controles que impidan publicar productos a usuarios sin rol vendedor.
9. No se encontraron controles para impedir operaciones comerciales a vendedores inactivos.

### Matriz de cumplimiento

| Regla | Resultado |
|---|---|
| Relación vendedor-usuario | Conforme |
| Un perfil por usuario | Conforme |
| Datos comerciales | Conforme estructuralmente |
| Creación del perfil | Pendiente |
| Edición del perfil | Pendiente |
| Estados del vendedor | Parcial |
| Autorización para publicar | Pendiente |
| Desactivación comercial | Pendiente |
| Protección de productos e historial | Parcial, mediante restricciones de eliminación |

### Comportamiento esperado

- Cada vendedor debe estar asociado a una cuenta existente.
- La cuenta debe conservar su rol comprador.
- Un usuario sin rol vendedor no debe poder publicar productos.
- Un vendedor inactivo no debe poder publicar ni modificar productos.
- Desactivar un perfil comercial no debe eliminar productos ni historial de operaciones.
- La eliminación de perfiles deberá considerar sus productos y solicitudes relacionadas.

### Conclusión

La estructura de la tabla y sus relaciones es adecuada para representar perfiles comerciales, pero todavía no existen las operaciones de administración ni los controles de autorización.

## 4.3 Productos y categorías

### Elementos revisados

- Modelo Categoria.
- Modelo Producto.
- Migraciones de categorías y productos.
- Relaciones con vendedores, inventarios, favoritos, carritos y pedidos.
- Campos de imágenes y video.
- Restricciones de eliminación.

### Hallazgos

1. Categoria cuenta con nombre, descripción y campo activa.
2. Producto contiene vendedor, categoría, nombre, descripción, precio y estado activo.
3. La estructura permite almacenar una imagen principal, seis imágenes adicionales y un video opcional.
4. La relación entre producto y vendedor está definida mediante id_vendedor.
5. La relación entre producto y categoría está definida mediante id_categoria.
6. Las claves foráneas de vendedor y categoría utilizan RESTRICT.
7. Los productos relacionados con detalles de pedidos no pueden eliminarse debido a la restricción RESTRICT.
8. Los productos relacionados con carritos tampoco pueden eliminarse mientras exista esa referencia.
9. Los registros de favoritos se eliminan mediante CASCADE cuando se elimina un producto.
10. No se encontraron operaciones de creación, edición, eliminación o desactivación de productos.
11. No se encontraron operaciones para administrar categorías.
12. No se encontraron controles de propiedad que garanticen que un vendedor modifique únicamente sus propios productos.

### Matriz de cumplimiento

| Regla | Resultado |
|---|---|
| Relación producto-categoría | Conforme |
| Relación producto-vendedor | Conforme |
| Hasta siete imágenes opcionales | Conforme estructuralmente |
| Un video opcional | Conforme estructuralmente |
| Creación y edición de productos | Pendiente |
| Administración de categorías | Pendiente |
| Validación de vendedor propietario | Pendiente |
| Validación de categoría activa | Pendiente |
| Protección de productos con compras | Conforme estructuralmente |
| Administración del estado activo | Pendiente |

### Comportamiento esperado

- Un vendedor solo podrá administrar sus propios productos.
- No se podrán publicar productos si el vendedor está inactivo.
- No se podrán publicar productos en categorías inactivas.
- Las imágenes y el video serán opcionales, respetando los límites establecidos.
- No se permitirá eliminar un producto que tenga compras asociadas.
- La desactivación de un producto deberá conservar sus datos e historial.
- Antes de eliminar un producto deberán atenderse las referencias existentes en carritos y favoritos.

### Conclusión

La estructura de productos y categorías permite representar los datos necesarios, pero todavía no existe la lógica de administración ni las validaciones comerciales.

## 4.4 Inventarios

### Elementos revisados

- Modelo Inventario.
- Migración de inventarios.
- Relación Producto-Inventario.
- Campos stock_fisico y stock_reservado.
- Búsqueda de operaciones de actualización, reserva y liberación de existencias.

### Hallazgos

1. Cada inventario pertenece a un producto.
2. El campo id_producto tiene restricción UNIQUE, garantizando un inventario por producto.
3. stock_fisico y stock_reservado son enteros UNSIGNED.
4. Los valores negativos están impedidos por el tipo de columna.
5. La estructura no impide que stock_reservado sea superior a stock_fisico.
6. No se encontraron operaciones para calcular existencias disponibles.
7. No se encontraron operaciones para reservar o liberar unidades.
8. No se encontraron transacciones ni bloqueos de filas para proteger operaciones concurrentes.
9. No se encontró la creación automática de inventario al registrar productos.

### Regla de disponibilidad

La cantidad disponible deberá calcularse mediante:

**Stock disponible = stock físico - stock reservado**

### Matriz de cumplimiento

| Regla | Resultado |
|---|---|
| Un inventario por producto | Conforme |
| Evitar valores negativos | Conforme estructuralmente |
| Reservas no superiores al stock físico | Pendiente |
| Cálculo de disponibilidad | Pendiente |
| Creación automática de inventario | Pendiente |
| Reserva de unidades | Pendiente |
| Liberación de reservas | Pendiente |
| Protección ante compras simultáneas | Pendiente |

### Comportamiento esperado

1. Crear inventario cuando se registre un producto.
2. Calcular existencias disponibles.
3. Validar disponibilidad antes de aceptar solicitudes de compra.
4. Reservar unidades cuando se generen las solicitudes.
5. Liberar las unidades reservadas cuando el vendedor rechace.
6. Descontar del inventario físico en el momento definido por el flujo real de entrega, sin asumir que aceptar una solicitud significa que el producto ya fue entregado.
7. Utilizar transacciones y bloqueos adecuados para evitar sobreventa.

### Conclusión

La estructura representa correctamente el inventario físico y reservado, pero todavía no existe la lógica que garantiza su consistencia durante las operaciones comerciales.

## 4.5 Favoritos y carritos

### Elementos revisados

- Modelo Favorito.
- Modelo Carrito.
- Modelo ItemCarrito.
- Migraciones y claves compuestas.
- Relaciones con Usuario y Producto.
- Búsqueda de operaciones de favoritos y carrito.

### Hallazgos

1. La tabla favoritos utiliza una clave primaria compuesta por id_usuario e id_producto.
2. Esta clave impide registrar dos veces el mismo producto como favorito para un usuario.
3. La tabla carritos tiene una clave foránea hacia usuarios.
4. La tabla carritos no tiene una restricción UNIQUE sobre id_usuario.
5. La relación actual Usuario-Carrito es HasMany.
6. La tabla items_carrito tiene una restricción UNIQUE sobre id_carrito e id_producto.
7. La cantidad es UNSIGNED, por lo que no permite negativos, pero sí permite cero.
8. No se encontraron operaciones para agregar o eliminar favoritos.
9. No se encontraron operaciones para agregar, modificar o eliminar productos del carrito.
10. No se encontraron validaciones de cantidad, disponibilidad o estado activo del producto.
11. La estructura permite que un carrito contenga productos de distintos vendedores.

### Matriz de cumplimiento

| Regla | Resultado |
|---|---|
| Evitar favoritos duplicados | Conforme |
| Agregar y eliminar favoritos | Pendiente |
| Evitar productos duplicados por carrito | Conforme |
| Cantidades positivas | Parcial |
| Validación de disponibilidad | Pendiente |
| Validación de producto activo | Pendiente |
| Carrito con múltiples vendedores | Conforme estructuralmente |
| Un carrito permanente por comprador | Pendiente |

### Comportamiento esperado

- Cada comprador tendrá un único carrito permanente.
- El carrito persistirá entre sesiones.
- Podrá contener productos de diferentes vendedores.
- Un producto no deberá aparecer duplicado dentro del mismo carrito; su cantidad deberá actualizarse.
- Las cantidades deberán ser mayores que cero.
- Se validará que los productos estén activos y tengan disponibilidad.
- Al solicitar la compra, los artículos deberán salir del carrito y pasar a las solicitudes generadas.

### Corrección identificada

La tabla carritos requiere una restricción UNIQUE sobre id_usuario para garantizar un único carrito por comprador. También deberá revisarse la relación Usuario-Carrito, que actualmente es HasMany y deberá ajustarse a HasOne.

### Conclusión

La estructura de favoritos y artículos de carrito cuenta con restricciones útiles, pero faltan las operaciones comerciales y la garantía estructural de un carrito único por comprador.

## 4.6 Pedidos y generación de solicitudes

### Elementos revisados

- Modelo Pedido.
- Modelo PedidoVendedor.
- Modelo DetallePedido.
- Migraciones correspondientes.
- Relaciones entre comprador, pedido general, vendedor y detalles.
- Búsqueda de operaciones de generación de pedidos y transacciones.

### Hallazgos

1. La tabla pedidos representa el pedido general y conserva al comprador, fecha, total de referencia y estado general.
2. La tabla pedidos_vendedor permite dividir un pedido general en solicitudes independientes.
3. La restricción UNIQUE sobre id_pedido e id_vendedor impide solicitudes duplicadas del mismo vendedor dentro del pedido.
4. La tabla detalles_pedido conserva el nombre del producto, cantidad y precio unitario de referencia.
5. Los precios históricos se representan mediante campos separados del precio actual del producto.
6. Las claves foráneas RESTRICT protegen los pedidos y sus detalles frente a eliminaciones.
7. No se encontraron operaciones que generen pedidos.
8. No se encontraron operaciones que agrupen productos por vendedor.
9. No se encontraron cálculos de subtotales o total general.
10. No se encontraron operaciones que integren el carrito con la generación de solicitudes.
11. No se encontraron transacciones para garantizar la creación completa de los registros.
12. No se encontró el vaciado del carrito después de una solicitud exitosa.

### Matriz de cumplimiento

| Regla | Resultado |
|---|---|
| Pedido general | Conforme estructuralmente |
| Solicitud por vendedor | Conforme estructuralmente |
| Detalles de productos | Conforme estructuralmente |
| Precio histórico | Conforme estructuralmente |
| Agrupación por vendedor | Pendiente |
| Cálculo de subtotales | Pendiente |
| Cálculo del total general | Pendiente |
| Validación de carrito | Pendiente |
| Transacción de generación | Pendiente |
| Vaciado del carrito | Pendiente |

### Comportamiento esperado

Al seleccionar «Solicitar compra», el sistema deberá:

1. Obtener el carrito del comprador.
2. Validar que contenga artículos.
3. Validar que los productos estén activos.
4. Comprobar las existencias disponibles.
5. Agrupar los productos por vendedor.
6. Crear un pedido general.
7. Crear una solicitud independiente por cada vendedor.
8. Crear los detalles correspondientes conservando nombre, cantidad y precio de referencia.
9. Calcular subtotales y total general.
10. Reservar las unidades correspondientes.
11. Vaciar el carrito únicamente cuando toda la operación haya concluido correctamente.

Toda la operación deberá ejecutarse dentro de una transacción.

### Conclusión

Las tablas y modelos permiten representar el flujo de pedidos, pero todavía no existe la lógica que transforma el carrito en pedidos y solicitudes independientes.

## 4.7 Respuestas de vendedores

### Elementos revisados

- Modelo PedidoVendedor.
- Modelo HistorialEstadoPedido.
- Migraciones de solicitudes e historial.
- Campo estado.
- Campo fecha_respuesta.
- Relaciones con pedido, vendedor, detalles, historial y notificaciones.
- Búsqueda global de operaciones de aceptación y rechazo.

### Hallazgos

1. Cada solicitud pertenece a un pedido general y a un vendedor.
2. Cada solicitud tiene su propio estado.
3. El campo fecha_respuesta permite registrar cuándo respondió el vendedor.
4. La tabla historial_estados_pedido permite conservar el estado, comentario, fecha del cambio y usuario responsable.
5. La relación con el usuario responsable permite NULL cuando se elimina el usuario.
6. No se encontraron operaciones para aceptar solicitudes.
7. No se encontraron operaciones para rechazar solicitudes.
8. No se encontraron validaciones para garantizar que responda el vendedor propietario.
9. No se encontraron controles para impedir respuestas repetidas o cambios de estado inválidos.
10. No se encontró la actualización automática de fecha_respuesta.
11. No se encontró la generación automática de registros de historial.
12. No se encontró la liberación de inventario reservado cuando se rechaza una solicitud.
13. No se encontraron notificaciones asociadas a las respuestas.

### Matriz de cumplimiento

| Regla | Resultado |
|---|---|
| Estado individual por vendedor | Conforme estructuralmente |
| Fecha de respuesta | Conforme estructuralmente |
| Historial de cambios | Conforme estructuralmente |
| Aceptar solicitud | Pendiente |
| Rechazar solicitud | Pendiente |
| Verificar vendedor propietario | Pendiente |
| Evitar respuestas repetidas | Pendiente |
| Registrar historial automáticamente | Pendiente |
| Liberar inventario al rechazar | Pendiente |
| Notificar al comprador | Pendiente |

### Comportamiento esperado

Cada vendedor deberá responder únicamente a sus propias solicitudes.

Las respuestas serán independientes. Por ejemplo, si un pedido contiene solicitudes para tres vendedores, uno podrá aceptar, otro rechazar y el tercero permanecer pendiente.

El estado general del pedido deberá determinarse considerando el resultado de todas las solicitudes relacionadas.

### Conclusión

La estructura permite representar las respuestas independientes de los vendedores y conservar su historial, pero no existe todavía la lógica que ejecuta dichas operaciones.

## 4.8 Notificaciones e historial

### Elementos revisados

- Modelo Notificacion.
- Modelo HistorialEstadoPedido.
- Migraciones correspondientes.
- Relaciones con Usuario y PedidoVendedor.
- Búsqueda global de generación y lectura de notificaciones.
- Búsqueda de servicios, eventos, listeners y clases de notificación.

### Hallazgos

1. Cada notificación pertenece a un usuario.
2. Una notificación puede relacionarse con una solicitud de vendedor.
3. Se dispone de los campos tipo y mensaje.
4. El campo leida inicia en false.
5. El campo fecha_lectura permite registrar cuándo se leyó.
6. Si se elimina el usuario, sus notificaciones se eliminan mediante CASCADE.
7. Si se elimina la solicitud relacionada, la notificación se conserva y su referencia queda en NULL.
8. Las relaciones Eloquent con Usuario y PedidoVendedor están definidas.
9. No se encontraron operaciones para generar notificaciones comerciales.
10. No se encontraron operaciones para marcar notificaciones como leídas.
11. No se encontraron operaciones para consultar notificaciones.
12. No se encontraron servicios, eventos, listeners ni clases propias para el sistema de notificaciones comerciales.
13. Las coincidencias de Notification::fake() corresponden a pruebas de recuperación de contraseña de Laravel, no al sistema comercial de TecnoMercado.

### Matriz de cumplimiento

| Regla | Resultado |
|---|---|
| Relación con usuario destinatario | Conforme |
| Relación con solicitud | Conforme |
| Tipo y mensaje | Conforme estructuralmente |
| Estado de lectura | Conforme estructuralmente |
| Fecha de lectura | Conforme estructuralmente |
| Generación de notificaciones | Pendiente |
| Consulta de notificaciones | Pendiente |
| Marcar como leída | Pendiente |
| Control de acceso al destinatario | Pendiente |
| Integración con pedidos y respuestas | Pendiente |

### Comportamiento esperado

- El vendedor deberá recibir una notificación cuando se genere una solicitud dirigida a él.
- El comprador deberá recibir una notificación cuando el vendedor acepte o rechace.
- Cada usuario solo podrá consultar sus propias notificaciones.
- El sistema deberá registrar la fecha en que se marque una notificación como leída.
- Los cambios de estado de las solicitudes deberán quedar registrados en el historial.
- Las notificaciones y el historial deberán integrarse con las operaciones comerciales correspondientes.

### Conclusión

La estructura de notificaciones e historial es adecuada para representar la información necesaria, pero todavía no existe la lógica de generación, consulta, lectura e integración con las operaciones comerciales.

## 4.9 Integridad global

### Elementos revisados

Se consultaron las estructuras de las siguientes tablas mediante el comando artisan db:table:

- usuarios
- usuario_roles
- vendedores
- productos
- inventarios
- carritos
- items_carrito
- pedidos
- pedidos_vendedor
- detalles_pedido
- notificaciones
- historial_estados_pedido

También se consideraron las restricciones de integridad revisadas durante las actividades anteriores.

### Restricciones que cumplen su propósito

| Tabla o relación | Restricción | Resultado |
|---|---|---|
| usuario_roles | PK compuesta id_usuario, id_rol | Evita roles duplicados |
| vendedores | UNIQUE id_usuario | Un perfil por cuenta |
| productos | FK a vendedores y categorías con RESTRICT | Protege referencias |
| inventarios | UNIQUE id_producto | Un inventario por producto |
| items_carrito | UNIQUE id_carrito, id_producto | Evita duplicados |
| pedidos_vendedor | UNIQUE id_pedido, id_vendedor | Una solicitud por vendedor y pedido |
| detalles_pedido | FK con RESTRICT | Protege el historial de compras |
| notificaciones | FK a pedidos_vendedor con SET NULL | Conserva notificaciones |
| historial_estados_pedido | FK al usuario responsable con SET NULL | Conserva el historial |

### Hallazgos globales

1. La tabla carritos no tiene UNIQUE sobre id_usuario, por lo que permite más de un carrito para un comprador.
2. items_carrito.cantidad es UNSIGNED, pero permite valores iguales a cero.
3. inventarios.stock_reservado puede superar inventarios.stock_fisico porque no existe una restricción que compare ambos campos.
4. Los estados de vendedores, pedidos y solicitudes se almacenan como VARCHAR sin catálogos ni restricciones de valores.
5. No se encontraron operaciones transaccionales para generar pedidos y solicitudes.
6. No se encontraron bloqueos de filas para proteger las operaciones concurrentes sobre inventario.
7. Las tablas de negocio se encuentran prácticamente vacías, por lo que la revisión fue principalmente estructural y no permitió validar escenarios comerciales completos con datos reales.

### Matriz global

| Aspecto | Resultado |
|---|---|
| Claves primarias | Conforme |
| Claves foráneas principales | Conforme |
| Relaciones estructurales | Conforme |
| Restricciones UNIQUE principales | Conforme, con excepción del carrito |
| Protección de historial de compras | Conforme estructuralmente |
| Carrito único por comprador | Pendiente |
| Cantidades mayores que cero | Pendiente |
| Consistencia de inventario reservado | Pendiente |
| Catálogos y validación de estados | Pendiente |
| Transacciones comerciales | Pendiente |
| Pruebas con escenarios reales | Pendiente |

### Conclusión

La estructura general de la base de datos cuenta con relaciones y restricciones importantes para preservar la integridad referencial. Sin embargo, existen reglas que todavía no están garantizadas por la base de datos ni por la lógica de aplicación.

# 5. Consolidado general de hallazgos

Los hallazgos identificados durante la auditoría se agrupan de la siguiente manera.

## 5.1 Usuarios, roles y vendedores

- Asignación automática del rol comprador al registrarse.
- Conversión de comprador a vendedor sin perder el rol comprador.
- Creación y administración del perfil comercial.
- Definición de restricciones para los tres administradores.
- Implementación de activación y desactivación de cuentas y vendedores.
- Protección de operaciones según rol y propietario.

## 5.2 Productos y categorías

- Implementar operaciones de creación, edición y eliminación.
- Validar que el vendedor sea propietario del producto.
- Validar que el vendedor esté activo.
- Validar que la categoría esté activa.
- Implementar administración de categorías.
- Proteger productos con compras asociadas.

## 5.3 Inventarios

- Crear inventario automáticamente al registrar productos.
- Calcular stock disponible.
- Impedir reservas superiores al stock disponible.
- Reservar unidades al generar solicitudes.
- Liberar reservas al rechazar solicitudes.
- Definir el momento correcto de descuento del stock físico.
- Utilizar transacciones y bloqueos para evitar sobreventa.

## 5.4 Favoritos y carritos

- Implementar operaciones de favoritos.
- Implementar operaciones del carrito.
- Garantizar un único carrito por comprador.
- Cambiar la relación Usuario-Carrito a HasOne y agregar UNIQUE a carritos.id_usuario.
- Impedir cantidades iguales a cero.
- Validar productos activos y disponibilidad.
- Mantener el carrito entre sesiones.
- Vaciar el carrito únicamente después de generar correctamente las solicitudes.

## 5.5 Pedidos y solicitudes

- Implementar la generación del pedido general.
- Agrupar productos por vendedor.
- Generar solicitudes independientes.
- Calcular subtotales y total general.
- Conservar los precios históricos.
- Crear detalles de pedido.
- Reservar inventario.
- Ejecutar todo el proceso en una transacción.

## 5.6 Respuestas de vendedores

- Implementar aceptación y rechazo.
- Verificar la propiedad de cada solicitud.
- Validar transiciones de estado.
- Registrar fecha de respuesta.
- Generar historial automáticamente.
- Liberar inventario cuando corresponda.
- Calcular el estado general del pedido.

## 5.7 Notificaciones e historial

- Generar notificaciones al crear solicitudes.
- Notificar al comprador al recibir respuestas.
- Consultar notificaciones propias.
- Marcar notificaciones como leídas.
- Registrar fechas de lectura.
- Integrar el historial con los cambios de estado.
- Restringir el acceso a notificaciones ajenas.

## 5.8 Integridad general

- Corregir la restricción de carrito único.
- Validar cantidades positivas.
- Garantizar la consistencia entre stock físico y reservado.
- Definir catálogos o validaciones para estados.
- Implementar transacciones y bloqueos donde sean necesarios.
- Agregar pruebas con escenarios de múltiples vendedores y respuestas independientes.

# 6. Decisiones y reglas que deberán conservarse durante la implementación

Las siguientes decisiones son fundamentales y no deberán modificarse accidentalmente durante las siguientes etapas:

1. TecnoMercado no procesa pagos.
2. TecnoMercado no gestiona envíos ni entregas.
3. El comprador y el vendedor acuerdan directamente la entrega.
4. Una cuenta puede tener simultáneamente los roles comprador y vendedor.
5. Todo usuario nuevo se registra como comprador.
6. La conversión a vendedor agrega el rol, no sustituye el rol comprador.
7. Cada comprador tiene un único carrito permanente.
8. El carrito puede contener productos de diferentes vendedores.
9. Cada vendedor responde de manera independiente a su solicitud.
10. Los pedidos conservan precios históricos de referencia.
11. Las operaciones de generación de solicitudes deben ser transaccionales.
12. Los productos con compras asociadas no pueden eliminarse.
13. El rechazo de una solicitud debe liberar las unidades reservadas correspondientes.
14. La aceptación de una solicitud no significa automáticamente que el producto haya sido entregado.

# 7. Limitaciones de la auditoría

La auditoría se realizó sobre el código, modelos, migraciones, relaciones y estructura actual de la base de datos.

Se identifican las siguientes limitaciones:

- Las tablas comerciales se encuentran prácticamente vacías.
- No fue posible comprobar flujos completos de compra con registros reales.
- No fue posible validar escenarios concurrentes de inventario.
- Las reglas de los tres administradores todavía requieren definición.
- La revisión de una estructura existente no demuestra que una funcionalidad esté operativa.
- Los resultados describen el estado encontrado durante esta auditoría y deberán actualizarse cuando se implementen las funcionalidades.

Por lo tanto, los resultados distinguen entre cumplimiento estructural y funcionamiento implementado.

# 8. Estado de la etapa

| Actividad | Descripción | Estado |
|---|---|---|
| 4.1 | Usuarios y roles | Cerrada |
| 4.2 | Vendedores | Cerrada |
| 4.3 | Productos y categorías | Cerrada |
| 4.4 | Inventarios | Cerrada |
| 4.5 | Favoritos y carritos | Cerrada |
| 4.6 | Pedidos y solicitudes | Cerrada |
| 4.7 | Respuestas de vendedores | Cerrada |
| 4.8 | Notificaciones e historial | Cerrada |
| 4.9 | Integridad global | Cerrada |
| 4.10 | Informe final | Documentado |

**Resultado:** las actividades de auditoría de reglas de negocio han sido completadas y documentadas.

# 9. Conclusión técnica

La auditoría de reglas de negocio permitió comprobar que TecnoMercado cuenta con una estructura de base de datos y modelos Eloquent capaz de representar los principales elementos de la plataforma: usuarios, roles, vendedores, productos, inventarios, favoritos, carritos, pedidos, solicitudes, notificaciones e historial.

También permitió identificar que una parte importante de las reglas de negocio todavía no cuenta con operaciones implementadas. Entre los puntos de mayor importancia se encuentran la asignación y administración de roles, la gestión de perfiles comerciales, las operaciones de inventario, la generación transaccional de pedidos, las respuestas independientes de vendedores y el sistema de notificaciones.

Se identificaron además ajustes estructurales pendientes, especialmente la garantía de un único carrito por comprador, la validación de cantidades positivas y la consistencia entre inventario físico y reservado.

La etapa se considera concluida desde el punto de vista de auditoría y documentación. Los hallazgos se conservarán como base para las siguientes etapas de implementación, validación, pruebas e integración.

**No se realizaron modificaciones al código fuente, migraciones, estructura de la base de datos ni registros comerciales durante esta etapa.**

---

# 10. Transición a la etapa 5

La siguiente etapa será la implementación de servicios y operaciones de negocio.

Se recomienda comenzar por los componentes fundamentales que serán utilizados por múltiples módulos:

1. Servicios de usuarios, roles y perfiles de vendedor.
2. Servicios de productos y categorías.
3. Servicios de inventario y reservaciones.
4. Servicios de favoritos y carrito.
5. Servicio transaccional para generar pedidos y solicitudes.
6. Servicio para aceptar y rechazar solicitudes.
7. Integración de notificaciones e historial.

Cada implementación deberá acompañarse de validaciones y pruebas, respetando las reglas documentadas en este informe.

**Fin del reporte técnico — Etapa 4.**

# REPORTE DE AVANCE – PUNTO 5: IMPLEMENTACIÓN DE SERVICIOS Y OPERACIONES DEL BACKEND

**Proyecto:** TecnoMercado  
**Carrera:** Ingeniería en Sistemas Computacionales  
**Institución:** Instituto Tecnológico Superior de Motul  
**Etapa:** Desarrollo del backend  
**Punto:** 5. Implementación de servicios y operaciones del backend  
**Fecha de finalización:** 18 de noviembre de 2026  
**Estado:** COMPLETADO

---

## 1. Introducción

Durante el desarrollo del punto 5 del proyecto TecnoMercado se llevó a cabo la implementación de los servicios encargados de gestionar las principales operaciones y reglas de negocio de la aplicación.

El objetivo de esta actividad fue centralizar la lógica del backend en clases de servicio, evitando concentrar las operaciones directamente en los controladores y facilitando la reutilización, mantenimiento y comprobación de las funcionalidades.

Se trabajó con Laravel 13, PHP 8.5 y MySQL 8.4, utilizando Eloquent ORM para la interacción con la base de datos y transacciones para proteger las operaciones que requieren consistencia.

La implementación se realizó considerando la estructura existente de la base de datos y respetando las reglas de negocio definidas para TecnoMercado.

## 2. Objetivo

Implementar los servicios del backend necesarios para administrar usuarios, vendedores, categorías, productos, inventario, favoritos, carrito de compras, pedidos y respuestas de los vendedores.

Asimismo, integrar las operaciones entre estos módulos, establecer las reglas de negocio correspondientes y desarrollar pruebas automatizadas para comprobar su funcionamiento.

## 3. Servicios implementados

Se desarrollaron nueve servicios principales:

| N.º | Servicio                   | Responsabilidad                                                                                |
| --- | -------------------------- | ---------------------------------------------------------------------------------------------- |
| 1   | `UsuarioService`           | Gestión de operaciones de usuarios y asignación de roles conforme a las reglas del sistema.    |
| 2   | `VendedorService`          | Conversión de usuarios en vendedores y administración de su perfil.                            |
| 3   | `CategoriaService`         | Gestión de categorías y sus operaciones correspondientes.                                      |
| 4   | `ProductoService`          | Creación y administración de productos, incluyendo su inventario inicial.                      |
| 5   | `InventarioService`        | Control de existencias físicas, reservas y disponibilidad de productos.                        |
| 6   | `FavoritoService`          | Administración de los productos favoritos de los usuarios.                                     |
| 7   | `CarritoService`           | Administración del carrito permanente y sus productos.                                         |
| 8   | `PedidoService`            | Generación de pedidos a partir del carrito y reserva de inventario.                            |
| 9   | `RespuestaVendedorService` | Procesamiento de las respuestas individuales de los vendedores ante las solicitudes de pedido. |

También se realizaron modificaciones en los modelos relacionados con estas operaciones para incorporar atributos, relaciones y comportamientos necesarios.

## 4. Funcionalidades desarrolladas

### 4.1. Gestión de usuarios y vendedores

Se implementaron operaciones para administrar usuarios y perfiles de vendedor.

Se respetaron las siguientes reglas:

- El registro público asigna únicamente el rol de comprador.
- El rol de administrador no puede obtenerse mediante el registro público.
- Un comprador puede convertirse posteriormente en vendedor.
- La conversión a vendedor no elimina su capacidad de operar como comprador.
- El perfil de vendedor puede desactivarse y reactivarse sin eliminar la cuenta ni su historial.

### 4.2. Gestión de categorías y productos

Se implementaron servicios para administrar las categorías y los productos publicados en la plataforma.

La creación de productos contempla la generación de su registro de inventario correspondiente dentro de una transacción.

Se consideraron los siguientes datos para la creación de productos:

- Categoría.
- Nombre.
- Descripción.
- Precio.
- Existencia física inicial.

El inventario inicial comienza con cero unidades reservadas.

### 4.3. Control de inventario

Se implementó la lógica para mantener la consistencia de las existencias.

Se estableció la siguiente regla:

**Stock disponible = Stock físico − Stock reservado**

Las operaciones de inventario deben respetar estas condiciones:

- El stock físico no puede ser negativo.
- El stock reservado no puede ser negativo.
- El stock reservado no puede superar al stock físico.
- Las reservas se realizan durante la generación del pedido.
- Las reservas no disminuyen inmediatamente las existencias físicas.

### 4.4. Carrito de compras

Se implementó la administración del carrito de compras considerando que cada usuario mantiene un carrito permanente.

Entre las operaciones contempladas se encuentran:

- Agregar productos.
- Consultar el contenido del carrito.
- Actualizar cantidades.
- Eliminar productos.
- Validar la disponibilidad correspondiente.

Agregar productos al carrito no reserva inventario.

Cuando se genera un pedido, los productos correspondientes se procesan y los elementos del carrito se eliminan, conservando el registro del carrito permanente.

### 4.5. Generación de pedidos

Se desarrolló `PedidoService` para generar pedidos a partir del carrito del comprador.

El proceso contempla:

1. Identificar los productos incluidos en el carrito.
2. Validar las condiciones necesarias para generar el pedido.
3. Agrupar los productos de acuerdo con sus respectivos vendedores.
4. Crear el registro del pedido.
5. Generar las solicitudes correspondientes para cada vendedor.
6. Crear los detalles de los pedidos.
7. Reservar las cantidades correspondientes del inventario.
8. Vaciar los elementos del carrito sin eliminar el carrito permanente.
9. Retornar el pedido con sus relaciones necesarias.

Las operaciones se realizan mediante transacciones para evitar registros incompletos cuando ocurre un error durante el proceso.

### 4.6. Respuestas individuales de los vendedores

Se implementó `RespuestaVendedorService` para procesar las respuestas de los vendedores sobre los productos incluidos en las solicitudes de pedido.

Cada vendedor puede responder de manera independiente sobre los productos que le corresponden.

Se contemplaron los siguientes escenarios:

- Aceptar un producto disponible.
- Rechazar un producto disponible indicando el motivo.
- Indicar que un producto ya no está disponible.
- Rechazar un producto que no está disponible.
- Decidir si se debe desactivar la publicación del producto.
- Proporcionar una justificación cuando se decide mantener publicada una oferta que no está disponible.

También se incorporaron controles para impedir respuestas duplicadas y respuestas de usuarios que no sean responsables de atender la solicitud.

### 4.7. Actualización del inventario después de la respuesta

Se establecieron las siguientes operaciones:

| Respuesta del vendedor | Stock físico | Stock reservado |
| ---------------------- | -----------: | --------------: |
| Acepta el producto     |    Disminuye |       Disminuye |
| Rechaza el producto    |  Se conserva |       Disminuye |

De esta manera, cuando el vendedor acepta, las unidades reservadas se descuentan de las existencias físicas. Cuando rechaza, únicamente se libera la reserva.

Si el producto no está disponible y el vendedor decide dar de baja su publicación, el producto se marca como inactivo.

### 4.8. Historial y notificaciones

Se incorporó el registro histórico de las respuestas de los vendedores.

Se creó la tabla `historial_respuestas_producto`, destinada a conservar información como:

- Detalle del pedido relacionado.
- Usuario responsable de la respuesta.
- Estado anterior y nuevo.
- Disponibilidad del producto.
- Motivo de la respuesta.
- Decisión de dar de baja la publicación.
- Justificación para mantener la publicación.
- Fecha de respuesta.

También se modificó la estructura de notificaciones para permitir relacionar una notificación con el detalle del pedido correspondiente.

El comprador recibe notificaciones individuales cuando cada vendedor responde. No se genera una notificación de respuesta pendiente para el comprador.

## 5. Cambios realizados en la base de datos

Para soportar el flujo de respuestas de los vendedores se creó y ejecutó la siguiente migración:

`2026_11_18_143133_add_responses_to_order_details_and_notifications.php`

La migración incorporó los siguientes cambios:

### Tabla `detalles_pedido`

Se agregaron los campos:

- `estado_respuesta`
- `disponibilidad_producto`
- `motivo_respuesta`
- `dar_de_baja_publicacion`
- `justificacion_publicacion`
- `fecha_respuesta`

### Nueva tabla `historial_respuestas_producto`

Se creó una tabla para conservar el historial de las respuestas de los vendedores, incluyendo sus relaciones con los detalles de pedido y los usuarios responsables.

### Tabla `notificaciones`

Se agregó el campo nullable `id_detalle_pedido`, junto con su relación correspondiente, para asociar las notificaciones con los detalles de pedido.

La migración se ejecutó correctamente y quedó registrada como aplicada.

**Importante:** se conservó la base de datos existente `tecnomercado_laravel`. No se reconstruyeron las tablas ni se utilizó `migrate:fresh`.

## 6. Pruebas automatizadas

Se desarrollaron pruebas automatizadas para comprobar el funcionamiento de los servicios y sus reglas de negocio.

Se incorporaron pruebas relacionadas con:

- Usuarios.
- Vendedores.
- Categorías.
- Productos.
- Inventario.
- Favoritos.
- Carrito.
- Pedidos.
- Respuestas de vendedores.
- Autenticación.
- Actualización del perfil.

También se realizaron ajustes en las pruebas de autenticación y configuración de Pest para trabajar con transacciones sobre la base de datos MySQL de pruebas, evitando depender de SQLite, cuyo controlador no se encontraba disponible en el entorno utilizado.

### 6.1. Resultado de la suite completa

La ejecución final de las pruebas automatizadas presentó el siguiente resultado:

| Indicador         | Resultado |
| ----------------- | --------: |
| Pruebas aprobadas |       128 |
| Pruebas omitidas  |        13 |
| Pruebas fallidas  |         0 |
| Aserciones        |       279 |

**Resultado:** la suite completa terminó sin pruebas fallidas.

Las 13 pruebas omitidas se mantienen identificadas como omitidas y no se contabilizan como pruebas aprobadas.

## 7. Archivos incorporados y modificados

El conjunto de cambios publicado incluyó:

- Servicios de negocio.
- Modelos y relaciones de Eloquent.
- Migración para respuestas de vendedores.
- Seeder de roles.
- Ajustes en el registro de usuarios.
- Pruebas automatizadas de servicios.
- Ajustes en pruebas de autenticación y perfil.
- Configuración de pruebas.

En total, el commit de esta unidad incorporó cambios en **34 archivos**, con **5,013 líneas añadidas y 37 eliminadas**.

## 8. Publicación en GitHub

Una vez finalizada la implementación y comprobadas las pruebas, se prepararon los cambios y se creó un commit para registrar el avance.

**Datos del commit:**

| Campo                 | Valor                                       |
| --------------------- | ------------------------------------------- |
| Rama                  | `main`                                      |
| Identificador         | `7112aa3`                                   |
| Mensaje               | `Implementa servicios y flujos del backend` |
| Archivos modificados  | 34                                          |
| Inserciones           | 5,013                                       |
| Eliminaciones         | 37                                          |
| Estado de publicación | Exitoso                                     |

El commit se publicó correctamente en el repositorio:

https://github.com/bacaaaaaaaaaaaaaaaaaaab/TecnoMercado.git

La rama local `main` quedó sincronizada con `origin/main` y el directorio de trabajo quedó limpio después de la publicación.

## 9. Resultado obtenido

Con la finalización de este punto se cuenta con una base de servicios para las principales operaciones del negocio de TecnoMercado.

Los módulos de usuarios, vendedores, categorías, productos, inventario, favoritos, carrito, pedidos y respuestas de vendedores disponen de sus respectivas clases de servicio.

También se integraron las operaciones relacionadas con la generación de pedidos, reservas de inventario, respuestas independientes de vendedores, historial y notificaciones.

Las pruebas automatizadas permiten comprobar el comportamiento implementado y detectar regresiones durante las siguientes etapas de desarrollo.

## 10. Conclusión

El punto 5 permitió completar una parte fundamental de la arquitectura del backend de TecnoMercado, centralizando las operaciones del negocio en servicios independientes y estableciendo las reglas necesarias para mantener la consistencia de la información.

La implementación de los procesos de carrito, pedidos e inventario, junto con el flujo de respuestas de vendedores, representa un avance importante para el funcionamiento de la plataforma como intermediaria entre compradores y vendedores.

La unidad quedó respaldada mediante pruebas automatizadas y publicada en GitHub.

**Estado final del punto 5: COMPLETADO.**

Las siguientes actividades corresponden a las etapas posteriores del proyecto, particularmente la revisión y cierre de validaciones, permisos, seguridad, notificaciones, autenticación, integración y revisión técnica, antes de iniciar el desarrollo del dashboard y las interfaces del frontend.

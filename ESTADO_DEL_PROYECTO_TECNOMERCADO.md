# TecnoMercado --- Avance del proyecto

**Proyecto:** TecnoMercado\
**Institución:** Instituto Tecnológico Superior de Motul\
**Carrera:** Ingeniería en Sistemas Computacionales\
**Semestre:** Séptimo\
**Grupo:** A\
**Tecnología actual:** Laravel 13, PHP 8.3+, MySQL 8.4 y Git\
**Estado de este documento:** 5 de noviembre de 2026

------------------------------------------------------------------------

## 1. Descripción general

TecnoMercado es una plataforma web de intermediación para la publicación
y consulta de productos ofrecidos por vendedores. Los compradores pueden
reunir productos de distintos vendedores en un carrito y enviar una
solicitud de compra.

La plataforma **no procesa pagos ni administra envíos**. Cuando el
comprador solicita la compra, el sistema registra la solicitud y
notifica al vendedor correspondiente. El vendedor se pone en contacto
con el comprador para acordar directamente el pago y la entrega o
recogida del producto.

## 2. Tecnologías y entorno de desarrollo

  Componente                        Tecnología
  --------------------------------- --------------------
  Framework del servidor            Laravel 13.39.0
  Lenguaje                          PHP 8.3 o superior
  Base de datos                     MySQL 8.4.11
  Interfaz prevista                 Livewire
  Estilos                           Tailwind CSS local
  Control de versiones              Git
  Repositorio remoto                GitHub
  IDE principal                     PhpStorm
  IDE previsto para vistas          WebStorm
  Sistema operativo de desarrollo   Windows

**Carpeta del proyecto:** `C:\Proyectos\TecnoMercadoLaravel`\
**Base de datos activa:** `tecnomercado_laravel`

> La base de datos anterior `tecnomercado_db`, correspondiente al
> proyecto previo, se mantiene separada y no debe modificarse durante el
> desarrollo de esta versión Laravel.

## 3. Alcance y reglas principales del sistema

-   Se contemplan tres roles: comprador, vendedor y administrador.
-   Una misma cuenta puede tener los roles de comprador y vendedor.
-   El sistema contempla únicamente tres personas con permisos de
    administrador.
-   TecnoMercado funciona como intermediario entre compradores y
    vendedores.
-   No se implementará procesamiento de pagos dentro de la plataforma.
-   No se implementará administración de entregas o servicios de
    paquetería.
-   El carrito puede contener productos de diferentes vendedores.
-   Al solicitar una compra, se registra la solicitud y se notifica a
    cada vendedor involucrado.
-   El vendedor puede aceptar o rechazar la solicitud y contactar al
    comprador por medios externos.
-   Los importes mostrados en las solicitudes son valores de referencia;
    no representan pagos procesados por la plataforma.
-   No se utilizará una tabla independiente para direcciones.
-   No se utilizará una tabla independiente para imágenes de productos.
    Los campos de imágenes y video se encuentran en la tabla
    `productos`.
-   Cada producto contempla una imagen principal, hasta seis imágenes
    adicionales y un video opcional.

## 4. Base de datos

La base de datos `tecnomercado_laravel` ya fue creada y las migraciones
de las tablas principales se ejecutaron correctamente.

### 4.1 Tablas principales del proyecto

  -------------------------------------------------------------------------------
                           N.º Tabla                        Propósito
  ---------------------------- ---------------------------- ---------------------
                             1 `usuarios`                   Almacenar las cuentas
                                                            de usuario.

                             2 `roles`                      Definir los roles
                                                            disponibles.

                             3 `usuario_roles`              Relacionar usuarios
                                                            con uno o varios
                                                            roles.

                             4 `vendedores`                 Almacenar el perfil
                                                            comercial de los
                                                            vendedores.

                             5 `categorias`                 Organizar los
                                                            productos por
                                                            categorías.

                             6 `productos`                  Almacenar la
                                                            información, precio y
                                                            rutas de archivos de
                                                            los productos.

                             7 `inventarios`                Controlar existencias
                                                            físicas y reservadas.

                             8 `favoritos`                  Relacionar usuarios
                                                            con sus productos
                                                            favoritos.

                             9 `carritos`                   Registrar los
                                                            carritos de los
                                                            usuarios.

                            10 `items_carrito`              Registrar los
                                                            productos y
                                                            cantidades de cada
                                                            carrito.

                            11 `pedidos`                    Registrar la
                                                            solicitud general de
                                                            compra del comprador.

                            12 `pedidos_vendedor`           Separar una solicitud
                                                            general por vendedor
                                                            involucrado.

                            13 `detalles_pedido`            Conservar los
                                                            productos, cantidades
                                                            y precios de
                                                            referencia
                                                            solicitados.

                            14 `notificaciones`             Registrar avisos
                                                            dirigidos a los
                                                            usuarios.

                            15 `historial_estados_pedido`   Conservar los cambios
                                                            de estado de cada
                                                            solicitud por
                                                            vendedor.
  -------------------------------------------------------------------------------

### 4.2 Relaciones principales

-   `usuarios` y `roles` tienen una relación muchos a muchos mediante
    `usuario_roles`.
-   Un usuario puede tener un perfil de vendedor en `vendedores`.
-   Un vendedor puede publicar muchos productos.
-   Cada producto pertenece a una categoría y tiene un registro de
    inventario.
-   Los usuarios pueden guardar productos en favoritos.
-   Un carrito pertenece a un usuario y contiene varios artículos.
-   Un pedido pertenece a un comprador y puede dividirse en varios
    registros de `pedidos_vendedor`.
-   Cada registro de `pedidos_vendedor` pertenece a un vendedor y
    contiene varios detalles.
-   Los detalles conservan el nombre y el precio de referencia del
    producto al momento de la solicitud.
-   Los usuarios reciben notificaciones.
-   Cada solicitud por vendedor puede tener un historial de estados y
    cambios.

### 4.3 Reglas relevantes de la base de datos

-   Las claves primarias utilizan nombres personalizados como
    `id_usuario`, `id_producto` e `id_pedido`.
-   Se definieron claves foráneas para mantener la integridad
    referencial.
-   El inventario distingue entre `stock_fisico` y `stock_reservado`. La
    disponibilidad se calcula como existencias físicas menos existencias
    reservadas.
-   Se establecieron restricciones únicas para evitar duplicados en
    relaciones que no deben repetirse, como el correo del usuario y el
    producto dentro de un mismo carrito.
-   Los detalles de una solicitud por vendedor hacen referencia a
    `pedidos_vendedor`, no directamente a `pedidos`.
-   El historial de estados también hace referencia a
    `pedidos_vendedor`.

### 4.4 Tablas técnicas de Laravel

Además de las 15 tablas del negocio, la instalación de Laravel contiene
tablas técnicas como `migrations`, `sessions`, `cache`, `jobs` y otras
relacionadas con el funcionamiento del framework. Estas tablas no forman
parte de las 15 tablas principales del modelo de negocio y se conservan
para los servicios internos de Laravel.

## 5. Migraciones completadas

Se crearon y ejecutaron las migraciones correspondientes a las 15 tablas
principales. La revisión realizada en MySQL confirmó que las tablas
existen en `tecnomercado_laravel`.

También se verificó la estructura de `productos`, incluyendo los campos
para la imagen principal, las imágenes adicionales y el video.

**Importante:** las migraciones ya ejecutadas no deben eliminarse ni
recrearse para continuar el desarrollo. Los ajustes futuros deberán
realizarse mediante nuevas migraciones. No utilizar
`php artisan migrate:fresh` en esta base de datos.

## 6. Estado actual del desarrollo

### Completado

-   [x] Instalación y configuración inicial de Laravel.
-   [x] Preparación del entorno PHP y Composer.
-   [x] Configuración de conexión con MySQL.
-   [x] Creación y selección de la base de datos `tecnomercado_laravel`.
-   [x] Definición del modelo relacional de datos.
-   [x] Creación de las 15 tablas principales mediante migraciones.
-   [x] Ejecución correcta de las migraciones.
-   [x] Verificación de existencia de las tablas en MySQL.
-   [x] Verificación de la estructura de la tabla `productos`.
-   [x] Inicialización del repositorio Git.
-   [x] Configuración del repositorio remoto de GitHub.
-   [x] Verificación de que `.env` está excluido del control de
    versiones.
-   [x] Existencia de `.env.example` para documentar la configuración
    del entorno.

### Pendiente

-   [ ] Crear y configurar los 15 modelos Eloquent.
-   [ ] Definir las relaciones entre modelos.
-   [ ] Adaptar la autenticación para utilizar la tabla `usuarios`, el
    campo `correo` y el campo `contrasena`.
-   [ ] Implementar registro, inicio y cierre de sesión.
-   [ ] Configurar recuperación y restablecimiento de contraseñas.
-   [ ] Definir hashing y protección de datos sensibles.
-   [ ] Revisar restricciones, índices e integridad referencial.
-   [ ] Determinar qué tablas requieren eliminación lógica
    (`SoftDeletes`).
-   [ ] Crear validaciones mediante `validate()` y clases Form Request
    cuando convenga.
-   [ ] Crear controladores de recursos y rutas CRUD.
-   [ ] Implementar autorización mediante Policies y, cuando sea
    necesario, Gates.
-   [ ] Configurar almacenamiento público para imágenes y videos.
-   [ ] Implementar paginación, ordenamiento y mensajes flash.
-   [ ] Realizar pruebas funcionales y de seguridad.
-   [ ] Integrar progresivamente las vistas con Livewire y Tailwind CSS.

## 7. Próxima etapa de trabajo

La siguiente etapa será preparar la capa de modelos de Laravel mediante
Eloquent.

Se crearán los modelos:

`Usuario`, `Rol`, `UsuarioRol`, `Vendedor`, `Categoria`, `Producto`,
`Inventario`, `Favorito`, `Carrito`, `ItemCarrito`, `Pedido`,
`PedidoVendedor`, `DetallePedido`, `Notificacion` e
`HistorialEstadoPedido`.

Cada modelo deberá configurarse con su tabla, clave primaria, atributos
asignables, conversiones de datos y relaciones. El modelo `Usuario`
tendrá una configuración especial para integrarse con el sistema de
autenticación de Laravel.

Después se trabajará en autenticación, autorización, validaciones, CRUD,
almacenamiento y pruebas de seguridad antes de avanzar a la construcción
completa de las vistas.

## 8. Comandos útiles

Ejecutar desde la carpeta raíz del proyecto:

``` powershell
# Verificar el estado de las migraciones
php artisan migrate:status

# Ejecutar migraciones pendientes
php artisan migrate

# Consultar las rutas registradas
php artisan route:list

# Limpiar la caché de configuración
php artisan config:clear

# Crear un modelo
php artisan make:model NombreModelo

# Crear un controlador de recursos
php artisan make:controller NombreController --resource

# Crear una clase de solicitud para validaciones
php artisan make:request NombreRequest

# Crear una política
php artisan make:policy NombrePolicy
```

No ejecutar comandos destructivos sobre la base de datos sin revisar
primero sus consecuencias.

## 9. Consideraciones de seguridad y colaboración

-   No subir el archivo `.env` a GitHub: contiene credenciales y
    configuración local.
-   Mantener `.env.example` sin contraseñas reales ni secretos.
-   No guardar contraseñas en texto plano; utilizar el hashing
    proporcionado por Laravel.
-   Validar los datos recibidos en el servidor, incluso si también se
    validan en el navegador.
-   Verificar permisos en el servidor mediante middleware y Policies;
    ocultar botones en la interfaz no es suficiente.
-   Proteger los formularios web con tokens CSRF.
-   Validar tipo, tamaño y contenido de los archivos antes de
    almacenarlos.
-   Evitar exponer información privada en respuestas, registros o
    mensajes de error.
-   Mantener las migraciones y el código fuente bajo control de
    versiones.

------------------------------------------------------------------------

**Fin del reporte de avance.**

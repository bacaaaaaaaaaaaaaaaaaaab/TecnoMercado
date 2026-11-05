# TecnoMercado – Reporte de avance del desarrollo

**Fecha:** 5 de noviembre de 2026  
**Proyecto:** TecnoMercado  
**Tecnología:** Laravel 13, PHP 8.3+, MySQL 8.4 y Livewire  
**Repositorio:** https://github.com/bacaaaaaaaaaaaaaaaaaaab/TecnoMercado

## 1. Objetivo de la actividad

En el contexto de Laravel y su ORM Eloquent, un modelo es una clase PHP que actúa como una representación orientada a objetos de una tabla de base de datos.
Cada modelo se asocia directamente con una tabla específica, donde cada instancia del modelo representa una fila de esa tabla.
Los modelos extienden la clase base Model y permiten realizar operaciones CRUD (Crear, Leer, Actualizar, Eliminar) de manera intuitiva sin escribir código SQL manualmente.
Siguen convenciones de nombres, como usar el singular para el modelo y el plural para la tabla, facilitando la asociación automática entre el código y la base de datos.

Continuar con la preparación del lado servidor de TecnoMercado después de completar la creación de las tablas principales de la base de datos. El objetivo de esta etapa fue preparar los modelos Eloquent y revisar la configuración de autenticación de Laravel antes de comenzar con los controladores, CRUD, validaciones, políticas de autorización e integración con las vistas.

## 2. Modelos Eloquent

Se revisó la carpeta `app/Models` para identificar los modelos existentes y evitar generar archivos duplicados.

Se identificaron los siguientes modelos:

- Usuario
- Rol
- UsuarioRol
- Vendedor
- Categoria
- Producto
- Inventario
- Favorito
- Carrito
- ItemCarrito
- Pedido
- User (modelo predeterminado de Laravel)

Se realizaron las siguientes actividades:

- Se identificó que el modelo `pedido.php` tenía el nombre de clase `pedido` en minúsculas.
- Se renombró el archivo a `Pedido.php` para mantener la convención de nombres de Laravel.
- Se generaron los modelos faltantes:
  - PedidoVendedor
  - DetallePedido
  - Notificacion
  - HistorialEstadoPedido

Con estas actividades se completó la creación de los 15 modelos correspondientes a las tablas principales del negocio, además del modelo predeterminado `User` de Laravel.

## 3. Revisión del modelo de autenticación

Se revisó el contenido de los siguientes archivos:

- `app/Models/Usuario.php`
- `app/Models/User.php`
- `config/auth.php`
- `config/fortify.php`

Se identificó que el modelo `Usuario` todavía utiliza la clase base `Illuminate\Database\Eloquent\Model`, por lo que aún necesita configurarse para trabajar con la autenticación de Laravel.

También se identificó que el modelo predeterminado `User` utiliza:

- Laravel Fortify.
- Verificación de correo electrónico.
- Autenticación de dos factores.
- Passkeys.
- Hashing de contraseñas.
- Notificaciones.

Se determinó que será necesario adaptar la autenticación para utilizar la tabla personalizada `usuarios`, cuyos campos son `id_usuario`, `correo` y `contrasena`, en lugar de depender de la tabla predeterminada `users`.

## 4. Revisión de rutas

Se ejecutó el comando:

`php artisan route:list`

Se identificaron 60 rutas registradas por Laravel y sus componentes instalados.

Entre ellas se encontraron:

- Inicio de sesión.
- Registro de usuarios.
- Cierre de sesión.
- Recuperación de contraseñas.
- Verificación de correo electrónico.
- Autenticación de dos factores.
- Passkeys.
- Configuración del perfil.
- Configuración de seguridad.
- Rutas de Livewire.

También se ejecutaron:

`php artisan route:list --path=login`

`php artisan route:list --path=register`

Esto permitió comprobar que las rutas de inicio de sesión y registro ya están disponibles mediante Laravel Fortify.

## 5. Estado actual del desarrollo

**Actividades completadas:**

- Creación de las 15 tablas principales mediante migraciones.
- Creación de los modelos correspondientes.
- Identificación y corrección del nombre del modelo Pedido.
- Creación de los cuatro modelos faltantes.
- Revisión del modelo predeterminado User.
- Inspección de la configuración de autenticación.
- Inspección de la configuración de Fortify.
- Revisión de las rutas existentes de Laravel y Livewire.

**Actividades pendientes:**

- Configuración de los modelos y sus claves primarias personalizadas.
- Definición de relaciones Eloquent.
- Adaptación de Usuario para la autenticación.
- Configuración del registro e inicio de sesión con la tabla `usuarios`.
- Configuración de hashing de contraseñas.
- Validaciones mediante Form Requests o validaciones de Laravel.
- Implementación de controladores y CRUD.
- Configuración de Soft Deletes donde corresponda.
- Configuración del almacenamiento de imágenes y videos.
- Implementación de Policies y Gates.
- Pruebas de funcionamiento y seguridad.
- Integración progresiva con Livewire.

## 6. Conclusión

En esta etapa se avanzó en la preparación de la estructura del servidor de TecnoMercado. Se completó la creación de los modelos principales y se revisaron los componentes de autenticación que Laravel proporciona por defecto.

El siguiente objetivo será configurar correctamente los modelos Eloquent y sus relaciones, para posteriormente adaptar el sistema de autenticación a la estructura personalizada de la base de datos.

El desarrollo continuará aprovechando las herramientas nativas de Laravel para reducir código innecesario, mejorar la seguridad y facilitar la implementación de los CRUD y las funcionalidades del sistema.

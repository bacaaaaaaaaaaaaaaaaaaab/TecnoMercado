# Reporte de Avance: Sincronización del Repositorio y Adaptación de la Autenticación

**Proyecto:** TecnoMercado  
**Tecnologías:** Laravel 13, Livewire 4, PHP 8.3 y MySQL 8.4  
**Fecha:** 06 de noviembre de 2026  
**Rama activa:** `main`  
**Repositorio:** GitHub - TecnoMercado

---

## 1. Objetivo del avance

Durante esta etapa se continuó con la preparación del proyecto TecnoMercado y la adaptación de los mecanismos de autenticación de Laravel Fortify a la estructura personalizada de la base de datos.

El objetivo principal es utilizar la tabla `usuarios` y el modelo `Usuario` como base para el registro, inicio de sesión y recuperación de contraseñas, evitando depender de la estructura predeterminada de Laravel (`users`).

También se documentaron las actividades de sincronización del repositorio y las modificaciones realizadas durante esta etapa.

## 2. Sincronización y organización del repositorio

Se continuó trabajando sobre la rama `main` del repositorio de GitHub.

Entre las actividades realizadas anteriormente se encuentran:

- Incorporación de la documentación de los modelos Eloquent.
- Verificación del archivo `.gitignore`.
- Protección del archivo `.env`, que contiene las credenciales locales.
- Conservación de `.env.example` como plantilla de configuración.
- Exclusión de dependencias y archivos generados automáticamente, como `vendor/`, `node_modules/` y `public/build/`.
- Incorporación de la estructura base de Laravel y Livewire al repositorio.

### Commits relacionados

| Commit | Descripción |
|---|---|
| `74354d9` | Agregar migraciones de la base de datos. |
| `c39c351` | Documentar el avance y la configuración de la base de datos. |
| `408a173` | Crear modelos Eloquent y documentar su implementación. |
| `ac588ca` | Ampliar la explicación de los modelos Eloquent. |
| `8b41e1a` | Agregar la estructura base de Laravel y Livewire. |

## 3. Adaptación de la autenticación

### 3.1. Diferencias entre Laravel y TecnoMercado

Laravel utiliza por defecto una estructura de autenticación basada en una tabla `users` y un modelo `User`.

Sin embargo, TecnoMercado utiliza una estructura personalizada:

| Elemento | Estructura predeterminada | Estructura de TecnoMercado |
|---|---|---|
| Tabla | `users` | `usuarios` |
| Modelo | `App\Models\User` | `App\Models\Usuario` |
| Llave primaria | `id` | `id_usuario` |
| Correo | `email` | `correo` |
| Contraseña almacenada | `password` | `contrasena` |
| Verificación de correo | `email_verified_at` | `correo_verificado_en` |

Por esta razón, fue necesario adaptar el modelo y las configuraciones de autenticación para que Laravel pueda trabajar con la estructura existente.

### 3.2. Adaptación del modelo Usuario

Se modificó `app/Models/Usuario.php` para que el modelo pueda participar en el sistema de autenticación de Laravel.

Los cambios incluyen:

- Extender la clase `Authenticatable`.
- Establecer `usuarios` como tabla correspondiente.
- Definir `id_usuario` como llave primaria.
- Configurar los campos permitidos para asignación masiva.
- Ocultar `contrasena` al serializar el modelo.
- Configurar la conversión de `contrasena` mediante hash.
- Configurar `correo_verificado_en` como fecha.
- Configurar `activo` como valor booleano.
- Sobrescribir `getAuthPasswordName()` para indicar que el campo de contraseña almacenada es `contrasena`.

### 3.3. Configuración de autenticación

Se modificó `config/auth.php` para utilizar `Usuario::class` como modelo del proveedor Eloquent.

Se conservó el identificador `users` del proveedor, ya que otras configuraciones de Laravel Fortify hacen referencia a dicho proveedor. Esto no significa que se utilice la tabla `users`; el modelo configurado apunta a `usuarios`.

### 3.4. Adaptación de las reglas de validación

Se modificó `app/Concerns/ProfileValidationRules.php`.

Ahora se validan los campos:

- `nombre`, con una longitud máxima de 100 caracteres.
- `apellidos`, con una longitud máxima de 150 caracteres.
- `correo`, con formato de correo electrónico y longitud máxima de 255 caracteres.

También se configuró la validación de unicidad del correo directamente sobre la columna `correo` de la tabla `usuarios`, utilizando `id_usuario` para excluir al usuario correspondiente cuando se realizan validaciones de actualización.

### 3.5. Adaptación del registro de usuarios

Se modificó `app/Actions/Fortify/CreateNewUser.php` para utilizar el modelo `Usuario`.

El registro ahora contempla los siguientes campos:

- `nombre`
- `apellidos`
- `correo`
- `contrasena`
- `activo`

El campo `activo` se establece inicialmente en `true`.

### 3.6. Adaptación del restablecimiento de contraseñas

Se modificó `app/Actions/Fortify/ResetUserPassword.php` para trabajar con el modelo `Usuario` y actualizar el campo `contrasena`.

La contraseña se procesa mediante el mecanismo de hash configurado en el modelo.

### 3.7. Configuración de Laravel Fortify

Se modificó `config/fortify.php` para establecer:

- `correo` como identificador de inicio de sesión.
- `correo` como campo de correo utilizado por Fortify.
- El registro de usuarios como funcionalidad habilitada.
- El restablecimiento de contraseñas como funcionalidad habilitada.

La verificación de correo electrónico, la autenticación de dos factores y las passkeys permanecen deshabilitadas temporalmente, ya que requieren adaptaciones adicionales.

Se conservaron comentarios como referencia para implementar estas funcionalidades posteriormente.

## 4. Adaptación del formulario de inicio de sesión

Se modificó `resources/views/pages/auth/login.blade.php` para comenzar a adaptar el formulario a la estructura personalizada.

Los cambios realizados incluyen:

- Cambiar el nombre del campo de correo de `email` a `correo`.
- Actualizar la recuperación del valor anterior mediante `old('correo')`.
- Cambiar la etiqueta del campo a «Correo electrónico».
- Establecer como ejemplo de placeholder `maria.23070008@itsmotul.edu.mx`.

El placeholder funciona como una sugerencia visual: desaparece cuando el usuario comienza a escribir.

El campo de contraseña conserva `name="password"`, debido a que Fortify utiliza ese identificador durante el proceso de autenticación. La correspondencia con la columna `contrasena` se realiza mediante la configuración del modelo `Usuario`.

La opción de recordar sesión permanece comentada temporalmente porque la tabla `usuarios` no cuenta actualmente con una columna `remember_token`.

## 5. Comprobaciones realizadas

Se ejecutaron comprobaciones de sintaxis PHP mediante `php -l` en los siguientes archivos:

- `app/Models/Usuario.php`
- `app/Actions/Fortify/CreateNewUser.php`
- `app/Actions/Fortify/ResetUserPassword.php`
- `app/Concerns/ProfileValidationRules.php`
- `config/auth.php`
- `config/fortify.php`

Todos los archivos comprobados reportaron que no presentan errores de sintaxis.

También se ejecutó:

```powershell
php artisan config:clear
```

El comando permitió limpiar la configuración almacenada en caché.

Estas comprobaciones verifican la sintaxis y la carga de configuración, pero no representan una prueba funcional completa del sistema de autenticación.

## 6. Actividades pendientes

Para continuar con la adaptación se deberán realizar las siguientes actividades:

1. Finalizar la adaptación del formulario de inicio de sesión.
2. Adaptar el formulario de registro para solicitar `nombre`, `apellidos`, `correo`, `contrasena` y su confirmación.
3. Adaptar el formulario de solicitud de recuperación de contraseña.
4. Adaptar el formulario de restablecimiento de contraseña.
5. Revisar la configuración del proveedor de recuperación de contraseñas y su compatibilidad con el campo `correo`.
6. Revisar la asignación del rol inicial correspondiente al nuevo usuario en `usuario_roles`.
7. Verificar que el inicio de sesión utilice correctamente el modelo `Usuario`.
8. Realizar pruebas funcionales de registro, inicio de sesión y recuperación de contraseña.
9. Revisar la compatibilidad de las demás funciones del starter kit con la estructura personalizada.

## 7. Conclusión

Durante esta etapa se avanzó en la integración de Laravel Fortify con la estructura de usuarios de TecnoMercado.

Se adaptaron el modelo `Usuario`, la configuración de autenticación, las reglas de validación, las acciones de registro y restablecimiento de contraseñas, así como una parte del formulario de inicio de sesión.

La adaptación todavía se encuentra en desarrollo. Aunque se verificó la sintaxis de los archivos PHP modificados y se limpió la configuración almacenada en caché, aún es necesario completar los formularios y realizar pruebas funcionales para confirmar el correcto funcionamiento de la autenticación.

Las modificaciones realizadas mantienen la estructura existente de la base de datos y no requieren alterar las migraciones que ya fueron ejecutadas.
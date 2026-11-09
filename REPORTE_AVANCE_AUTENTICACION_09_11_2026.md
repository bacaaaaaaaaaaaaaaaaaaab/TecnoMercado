# Reporte de avance – TecnoMercadoLaravel
**Fecha:** 09 de noviembre de 2026  
**Rama:** `main`  
**Proyecto:** TecnoMercado

## 1. Objetivo del trabajo

Continuar la adaptación del sistema de autenticación de Laravel Fortify para utilizar la estructura personalizada de TecnoMercado, cuya tabla principal de usuarios es `usuarios`, con los campos `correo` y `contrasena`.

También se corrigieron errores detectados durante las pruebas, se incorporaron elementos necesarios para la recuperación de contraseñas y se revisaron dependencias del proyecto.

## 2. Adaptación de autenticación

Se realizaron ajustes para integrar el sistema de autenticación de Laravel con el modelo personalizado `Usuario`.

Cambios principales:

- Configuración del proveedor de autenticación para utilizar `App\Models\Usuario`.
- Adaptación del inicio de sesión para utilizar `correo` en lugar de `email`.
- Adaptación del registro para utilizar los campos `nombre`, `apellidos`, `correo` y `contrasena`.
- Configuración de Fortify para trabajar con el campo personalizado `correo`.
- Adaptación de las validaciones de perfil y correo electrónico.
- Ajustes en las vistas de inicio de sesión, registro y recuperación de contraseña.
- Configuración de la recuperación de contraseña para enviar notificaciones al correo registrado en `usuarios`.

## 3. Corrección del restablecimiento de contraseñas

Se identificó una incompatibilidad entre los nombres de campos esperados por Laravel Fortify y los utilizados por la estructura personalizada de TecnoMercado.

Se corrigió el proceso para que:

- El formulario envíe `password` y `password_confirmation`, como espera Fortify.
- La contraseña se almacene en la columna `contrasena`.
- La recuperación identifique al usuario mediante `correo`.
- Se utilice el mecanismo de tokens de recuperación de Laravel.
- Se compruebe que los tokens no puedan reutilizarse después de completar el restablecimiento.

No fue necesario modificar archivos internos de `vendor`.

## 4. Compatibilidad del modelo Usuario

Se incorporaron ajustes en `app/Models/Usuario.php` para permitir la integración con los mecanismos de autenticación y recuperación de contraseñas de Laravel.

Entre ellos:

- Identificación de la contraseña mediante el campo `contrasena`.
- Obtención del correo de recuperación mediante `correo`.
- Configuración del destinatario de las notificaciones por correo.
- Uso de hashing para almacenar las contraseñas.
- Compatibilidad con las pruebas mediante el uso de factories.

## 5. Factory para usuarios

Se creó:

`database/factories/UsuarioFactory.php`

Esta factory permite generar registros de prueba compatibles con la estructura personalizada de la tabla `usuarios`.

Se utiliza para realizar pruebas automatizadas de autenticación, registro y recuperación de contraseñas.

## 6. Migración de remember_token

Se creó y ejecutó la migración:

`2026_11_08_165350_add_remember_token_to_usuarios_table.php`

Esta migración incorpora la columna `remember_token` a la tabla `usuarios`.

Su finalidad es permitir que Laravel gestione correctamente la renovación del token de sesión persistente durante determinados procesos de autenticación y recuperación de contraseñas.

La ejecución de la migración fue confirmada por Laravel con el resultado `DONE`.

La base de datos utilizada es `tecnomercado_laravel`.

## 7. Correcciones en las vistas de autenticación

Se realizaron ajustes en las siguientes vistas:

- `resources/views/pages/auth/login.blade.php`
- `resources/views/pages/auth/confirm-password.blade.php`
- `resources/views/pages/auth/forgot-password.blade.php`
- `resources/views/pages/auth/register.blade.php`
- `resources/views/pages/auth/reset-password.blade.php`

Se corrigieron elementos relacionados con los campos personalizados y se retiraron componentes de verificación mediante passkeys que no corresponden a las funciones habilitadas actualmente.

## 8. Pruebas automatizadas

Se adaptaron pruebas para utilizar el modelo `Usuario` y los campos personalizados de TecnoMercado.

Archivos de pruebas modificados:

- `tests/Feature/Auth/AuthenticationTest.php`
- `tests/Feature/Auth/RegistrationTest.php`
- `tests/Feature/Auth/PasswordResetTest.php`

### Resultados de las pruebas de autenticación

Se ejecutó:

`php artisan test tests/Feature/Auth --stop-on-failure`

Resultado:

| Resultado | Cantidad |
|---|---:|
| Pruebas aprobadas | 9 |
| Pruebas omitidas | 7 |
| Pruebas fallidas | 0 |
| Aserciones | 20 |

Las pruebas aprobadas corresponden al inicio de sesión, cierre de sesión, registro, visualización de pantallas y recuperación de contraseñas.

Las siete pruebas omitidas corresponden a funciones que actualmente están deshabilitadas en Fortify:

- Cinco pruebas de verificación de correo electrónico.
- Dos pruebas de autenticación de dos factores.

Estas omisiones son intencionales y no representan errores de ejecución.

## 9. Actualización y revisión de dependencias

Se ejecutó una actualización dirigida de componentes Symfony:

`composer update symfony/http-foundation symfony/http-kernel symfony/routing symfony/serializer symfony/yaml --with-all-dependencies`

Se actualizaron los componentes seleccionados de la versión `8.1.10` a `8.1.11`.

Posteriormente se ejecutó:

`composer audit`

Resultado: no se encontraron avisos de vulnerabilidades de seguridad en las dependencias revisadas.

El archivo `composer.lock` presenta modificaciones derivadas de la actualización.

## 10. Archivos principales involucrados

**Archivos creados:**

- `database/factories/UsuarioFactory.php`
- `database/migrations/2026_11_08_165350_add_remember_token_to_usuarios_table.php`

**Archivos modificados o involucrados en las correcciones:**

- `app/Models/Usuario.php`
- `app/Actions/Fortify/ResetUserPassword.php`
- `config/auth.php`
- `config/fortify.php`
- `resources/views/pages/auth/login.blade.php`
- `resources/views/pages/auth/confirm-password.blade.php`
- `resources/views/pages/auth/forgot-password.blade.php`
- `resources/views/pages/auth/register.blade.php`
- `resources/views/pages/auth/reset-password.blade.php`
- `tests/Feature/Auth/AuthenticationTest.php`
- `tests/Feature/Auth/RegistrationTest.php`
- `tests/Feature/Auth/PasswordResetTest.php`
- `composer.lock`

La lista definitiva de archivos que deberán incluirse en el commit se determinará mediante `git status` y la revisión de los cambios.

## 11. Pendientes de verificación

Antes de considerar concluida esta etapa:

1. Ejecutar la suite completa de pruebas con `php artisan test --stop-on-failure`.
2. Revisar y corregir cualquier fallo que aparezca fuera de las pruebas de autenticación.
3. Revisar los cambios pendientes con Git.
4. Comprobar que no se incluyan archivos temporales, credenciales ni archivos `.env`.
5. Actualizar el reporte con los resultados de la suite completa.

## 12. Estado general

La adaptación de autenticación y recuperación de contraseñas cuenta con nueve pruebas aprobadas y ninguna fallida dentro del conjunto de autenticación.

La migración de `remember_token` fue ejecutada correctamente y la revisión de dependencias no reportó vulnerabilidades.

**Estado de esta etapa:** validación de autenticación completada; validación general del proyecto pendiente.

**Importante:** todavía no se debe considerar este conjunto de cambios como confirmado en Git hasta revisar el estado del repositorio y completar las pruebas generales.

# REPORTE DE AVANCE — TECNOMERCADO
**Fecha:** 09 de noviembre de 2026  
**Proyecto:** TecnoMercadoLaravel  
**Tecnologías:** Laravel 13, PHP 8.5, MySQL 8.4, Livewire 4 y Tailwind CSS 4  
**Rama:** `main`

---

## 1. Objetivo del trabajo realizado

Durante las sesiones de trabajo correspondientes a «Justificación tablas relaciones (9)» y las conversaciones posteriores, se continuó con la preparación del backend de TecnoMercadoLaravel.

El trabajo se concentró principalmente en la configuración del entorno, la seguridad de dependencias, la adaptación del sistema de autenticación de Laravel a la estructura propia de TecnoMercado y la corrección de pruebas automatizadas.

Se mantuvo como prioridad validar el funcionamiento del servidor antes de comenzar con el desarrollo de las interfaces y funcionalidades visuales.

## 2. Configuración y verificación del proyecto

Se continuó verificando la configuración del proyecto y su conexión con la base de datos MySQL.

Se confirmó que la conexión activa utiliza:

- **Motor:** MySQL.
- **Base de datos:** `tecnomercado_laravel`.
- **Servidor:** `127.0.0.1`.
- **Puerto:** `3306`.

Se verificó que la base de datos activa es `tecnomercado_laravel` y no la base anterior `tecnomercado_db`.

También se continuó trabajando con la configuración de almacenamiento público de Laravel. Se ejecutó `php artisan storage:link` y Laravel confirmó la creación del enlace:

`public/storage` → `storage/app/public`.

## 3. Actualización de dependencias y seguridad

Se realizó una actualización específica de componentes Symfony utilizados por Laravel.

Se ejecutó:

`composer update symfony/http-foundation symfony/http-kernel symfony/routing symfony/serializer symfony/yaml --with-all-dependencies`

La actualización permitió instalar Symfony 8.1.11 en los componentes correspondientes.

Posteriormente se ejecutó `composer audit` y Composer informó que no encontró avisos de vulnerabilidades de seguridad en las dependencias auditadas.

El archivo `composer.lock` quedó modificado como resultado de esta actualización.

## 4. Adaptación de la autenticación a la estructura de TecnoMercado

Se continuó adaptando el sistema de autenticación para utilizar la tabla `usuarios` en lugar de depender de la estructura predeterminada de Laravel.

Se trabajó con el modelo `Usuario`, considerando las particularidades de la base de datos:

- Clave primaria: `id_usuario`.
- Correo electrónico: `correo`.
- Contraseña almacenada: `contrasena`.
- Estado de la cuenta: `activo`.

Se mantuvo la configuración de autenticación para que Laravel y Fortify utilicen el modelo `Usuario`.

También se conservaron los nombres de campos propios del proyecto en los formularios de registro y recuperación de contraseña, respetando los nombres que Laravel Fortify necesita recibir para procesar las operaciones de autenticación.

## 5. Correcciones en las pantallas de autenticación

Se corrigieron referencias relacionadas con Passkeys en las vistas de inicio de sesión y confirmación de contraseña.

Se eliminaron los componentes `<x-passkey-verify />` de:

- `resources/views/pages/auth/login.blade.php`
- `resources/views/pages/auth/confirm-password.blade.php`

La finalidad fue evitar errores al intentar renderizar componentes de Passkeys que no forman parte de las funcionalidades habilitadas en la configuración actual de Fortify.

Las funcionalidades de verificación de correo electrónico y autenticación de dos factores permanecen deshabilitadas intencionalmente.

## 6. Correcciones del restablecimiento de contraseña

Se continuó corrigiendo el proceso de recuperación de contraseña para que funcione con el modelo `Usuario`.

Se ajustaron las pruebas de `PasswordResetTest.php`, incluyendo la captura correcta de la variable `$usuario` dentro de las funciones utilizadas para verificar las notificaciones.

También se identificó que Laravel necesita una columna `remember_token` para completar correctamente determinadas operaciones relacionadas con el restablecimiento de contraseñas.

Para resolverlo sin modificar migraciones que ya habían sido ejecutadas, se creó una migración independiente:

`database/migrations/2026_11_08_165350_add_remember_token_to_usuarios_table.php`

La migración agrega el campo mediante `$table->rememberToken()` y contempla su eliminación en el método `down()`.

Se ejecutó `php artisan migrate` y Laravel confirmó que la migración se aplicó correctamente.

## 7. Creación de la fábrica de usuarios

Se creó:

`database/factories/UsuarioFactory.php`

Esta fábrica permite generar registros de prueba compatibles con la estructura de la tabla `usuarios`.

Se contemplan los campos:

- `nombre`
- `apellidos`
- `correo`
- `contrasena`
- `activo`

Esto permite utilizar `Usuario::factory()` en las pruebas automatizadas sin depender del modelo predeterminado `User`.

## 8. Adaptación y corrección de pruebas automatizadas

Se actualizaron las pruebas para que utilicen el modelo `Usuario` y los campos propios de TecnoMercado.

Archivos modificados:

- `tests/Feature/Auth/AuthenticationTest.php`
- `tests/Feature/Auth/PasswordResetTest.php`
- `tests/Feature/Auth/RegistrationTest.php`

Se corrigieron los datos enviados durante las operaciones de inicio de sesión, registro y restablecimiento de contraseña.

### Resultados de pruebas individuales

| Prueba | Resultado |
|---|---|
| PasswordResetTest | 4 aprobadas |
| AuthenticationTest | 4 aprobadas, 1 omitida |
| RegistrationTest | 2 aprobadas |

### Resultado del conjunto de pruebas de autenticación

Se ejecutó:

`php artisan test tests/Feature/Auth --stop-on-failure`

Resultado:

- **9 pruebas aprobadas.**
- **7 pruebas omitidas.**
- **0 pruebas fallidas.**
- **20 aserciones.**

Las pruebas omitidas corresponden a funcionalidades que permanecen deshabilitadas:

- 5 pruebas de verificación de correo electrónico.
- 2 pruebas de autenticación de dos factores.

Por lo tanto, el conjunto de pruebas de autenticación habilitado actualmente se ejecutó sin errores.

## 9. Archivos modificados y creados pendientes de registrar en Git

De acuerdo con el estado de Git del 9 de noviembre de 2026, se identificaron los siguientes cambios:

### Archivos modificados

- `composer.lock`
- `resources/views/pages/auth/confirm-password.blade.php`
- `resources/views/pages/auth/login.blade.php`
- `tests/Feature/Auth/AuthenticationTest.php`
- `tests/Feature/Auth/PasswordResetTest.php`
- `tests/Feature/Auth/RegistrationTest.php`

### Archivos nuevos

- `database/factories/UsuarioFactory.php`
- `database/migrations/2026_11_08_165350_add_remember_token_to_usuarios_table.php`
- `REPORTE_AVANCE_AUTENTICACION_09_11_2026.md`

## 10. Estado actual del repositorio

La rama de trabajo continúa siendo `main`.

El último commit registrado es:

`f34c2ba docs: agregar reporte de avance de autenticacion 06-11-2026`

El repositorio local se encuentra sincronizado con `origin/main`, pero los cambios descritos en este reporte todavía no han sido agregados a un nuevo commit.

## 11. Actividades pendientes

Antes de registrar los cambios en Git, se debe:

1. Ejecutar la suite completa mediante `php artisan test --stop-on-failure`.
2. Revisar y corregir cualquier prueba fallida que aparezca.
3. Verificar nuevamente los archivos modificados con `git status`.
4. Revisar el contenido del reporte y los cambios que se van a registrar.
5. Agregar los archivos correspondientes al área de preparación de Git.
6. Crear un commit descriptivo.
7. Enviar los cambios al repositorio remoto de GitHub.

**Importante:** La validación completa del proyecto todavía está pendiente. El resultado satisfactorio de las pruebas de autenticación no significa que toda la suite de pruebas esté aprobada.

---

## 12. Conclusión

Durante este periodo se avanzó en la adaptación y estabilización del sistema de autenticación de TecnoMercadoLaravel, corrigiendo incompatibilidades entre Laravel Fortify y la estructura personalizada de usuarios del proyecto.

También se incorporó la columna `remember_token` mediante una nueva migración, se creó una fábrica de usuarios para pruebas y se actualizaron dependencias relacionadas con la seguridad.

El conjunto de pruebas de autenticación terminó con 9 pruebas aprobadas, 7 omitidas por funcionalidades deshabilitadas y ningún fallo.

El siguiente objetivo es validar la suite completa de pruebas y posteriormente registrar los cambios en GitHub, manteniendo un historial de desarrollo organizado y documentado.
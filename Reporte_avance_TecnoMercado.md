REPORTE DE AVANCE DEL PROYECTO TECNOMERCADO
Continuidad de trabajo: auditoría de base de datos y preparación de auditoría Eloquent
Fecha de corte: 10 de noviembre de 2026

PROYECTO
Nombre: TecnoMercadoLaravel
Ruta local: C:\Proyectos\TecnoMercadoLaravel
Framework: Laravel 13.39.0
PHP: 8.5
Base de datos: MySQL 8.4.11
Base activa: tecnomercado_laravel
Tecnologías previstas: Laravel, MySQL, Livewire 4.4.8 y Tailwind CSS 4.
Repositorio: https://github.com/bacaaaaaaaaaaaaaaaaaaab/TecnoMercado.git
Rama: main

OBJETIVO Y FORMA DE TRABAJO
El objetivo es completar primero un backend consistente, probado y funcional. Después se desarrollarán las interfaces completas con Livewire y Tailwind.
No se desea trabajar con Vue ni centrar el proyecto en Blade como tecnología.
El trabajo se realiza por etapas, una actividad a la vez. Primero se recopila evidencia y se diagnostica; no se modifican archivos ni la base de datos durante una auditoría. No se crean commits hasta que las pruebas correspondientes estén correctas y el usuario autorice.
Se debe indicar expresamente cuándo comienza y cuándo termina cada actividad o etapa. Mantener el avance documentado y evitar repetir consultas ya confirmadas.
El usuario prefiere recibir un único bloque de comandos por turno, ejecutarlo y compartir el resultado antes de continuar.

CONTEXTO ACADÉMICO
Proyecto universitario de séptimo semestre de Ingeniería en Sistemas Computacionales, Instituto Tecnológico Superior de Motul, grupo A.
Docente de desarrollo web: Walter Ivan Manzanilla Yuit.

DECISIONES FUNCIONALES DEL SISTEMA
- Roles: vendedor, comprador y administrador. Se contemplan únicamente tres personas administradoras.
- Una misma cuenta puede desempeñar los roles de comprador y vendedor.
- No se utilizará una tabla de direcciones.
- TecnoMercado funciona como intermediario. No procesa pagos ni administra entregas o envíos.
- Un carrito puede contener productos de distintos vendedores y mostrar un total general.
- Al solicitar la compra, se generan las solicitudes correspondientes para los vendedores. Cada vendedor puede contactar al comprador y aceptar o rechazar su parte del pedido.
- El pedido general se divide en pedidos por vendedor.
- Los detalles del pedido conservan el nombre del producto y el precio de referencia para preservar el historial.
- No habrá una tabla separada de imágenes de productos. La tabla productos contempla hasta siete campos opcionales de imagen y un campo opcional de video.
- Un producto no debe poder eliminarse si tiene compras asociadas.
- El inventario distingue stock físico y stock reservado.

TRABAJO PREVIO DE AUTENTICACIÓN Y PERFIL
Se adaptó la autenticación al modelo personalizado App\Models\Usuario:
- Tabla principal: usuarios.
- Llave primaria: id_usuario.
- Campos principales: nombre, apellidos, correo, contrasena y activo.
- El modelo utiliza getAuthPasswordName() para indicar contrasena.
- Se configuraron los métodos de correo para recuperación de contraseña y notificaciones.
- Se agregó remember_token a usuarios mediante la migración 2026_11_08_165350_add_remember_token_to_usuarios_table.php, la cual ya fue ejecutada.
- El provider de autenticación utiliza App\Models\Usuario.
- Fortify tiene habilitados registro y recuperación de contraseña. La verificación de correo y la autenticación de dos factores están deshabilitadas intencionalmente.
- Se adaptó el perfil Livewire a los campos nombre, apellidos y correo.
- Se adaptó la eliminación de cuenta para comprobar la contraseña actual y cerrar sesión.
- Se incorporaron accesores name y email, además del método initials(), para mantener compatibilidad con componentes del Starter Kit y Flux.
- Se actualizó la fábrica UsuarioFactory para crear registros compatibles con usuarios. La fábrica UserFactory heredada utiliza campos distintos y no debe usarse para probar el modelo Usuario.
- Se actualizaron las pruebas del perfil para utilizar Usuario::factory().

PRUEBAS PREVIAS
Pruebas específicas de perfil:
Comando: php artisan test tests/Feature/Settings/ProfileUpdateTest.php --stop-on-failure
Resultado: 5 pruebas correctas, 16 aserciones.

Suite completa:
Comando: php artisan test --stop-on-failure
Resultado: 18 pruebas correctas, 13 omitidas, 41 aserciones y 0 fallidas.
Las pruebas omitidas corresponden a funciones intencionalmente deshabilitadas:
- Verificación de correo.
- Autenticación de dos factores y pruebas de seguridad asociadas.

GIT
Último commit de trabajo confirmado: b29b48e adaptar perfil al modelo de usuario personalizado.
Se confirmó que el commit fue enviado a origin/main y que el árbol de trabajo estaba limpio en ese momento.
Repositorio remoto: https://github.com/bacaaaaaaaaaaaaaaaaaaab/TecnoMercado.git
No crear nuevos commits sin pruebas y autorización del usuario.

HOJA DE RUTA GENERAL DEL BACKEND
1. Auditoría completa de base de datos.
2. Auditoría de modelos Eloquent.
3. Auditoría de relaciones Eloquent.
4. Auditoría de reglas de negocio.
5. Implementación de servicios y operaciones.
6. Validaciones, permisos y seguridad.
7. Notificaciones y procesos complementarios.
8. Revisión final de autenticación y cuentas.
9. Pruebas automatizadas por módulo.
10. Integración completa del backend.
11. Revisión técnica y optimización.
12. Cierre y entrega técnica.

ETAPA 1: AUDITORÍA DE BASE DE DATOS

Conexión y migraciones:
- La conexión predeterminada es mysql.
- La base de datos activa confirmada es tecnomercado_laravel, no tecnomercado_db.
- MySQL Server reportado: 8.4.11.
- php artisan migrate:status mostró 21 migraciones registradas como ejecutadas.
- php artisan db:show llegó a informar datos de la conexión, pero terminó con RuntimeException por falta de la extensión PHP intl al formatear la salida. No se interpretó como un fallo de conexión ni de la base de datos.
- Se consultó INFORMATION_SCHEMA directamente para obtener evidencia estructural.

Tablas:
Se identificaron 25 tablas en la base de datos:
Tablas de negocio:
usuarios, roles, usuario_roles, vendedores, categorias, productos, inventarios, favoritos, carritos, items_carrito, pedidos, pedidos_vendedor, detalles_pedido, notificaciones, historial_estados_pedido.

Tablas de infraestructura o heredadas de Laravel:
cache, cache_locks, failed_jobs, job_batches, jobs, migrations, passkeys, password_reset_tokens, sessions, users.

Columnas:
- Se inspeccionaron 168 columnas mediante INFORMATION_SCHEMA.
- Las tablas principales de negocio contienen las columnas esperadas de acuerdo con el diseño general.
- usuarios utiliza id_usuario, nombre, apellidos, correo, contrasena, activo, correo_verificado_en y remember_token.
- inventarios distingue stock_fisico y stock_reservado, ambos con valor predeterminado cero.
- productos tiene campos opcionales para imagen_principal, imagen_2 a imagen_7 y video.
- pedidos y pedidos_vendedor separan el pedido general de la solicitud dirigida a cada vendedor.
- detalles_pedido conserva nombre_producto y precio_unitario_referencia.
- Los estados y fechas obligatorios sin valor predeterminado deben asignarse explícitamente desde la aplicación.
- No se identificó una restricción CHECK registrada en MySQL. Las validaciones deberán estar garantizadas por Laravel y se evaluará posteriormente si conviene incorporar restricciones de base de datos.

Claves primarias, foráneas e índices:
- Se identificaron 21 claves foráneas.
- Todas las tablas tienen índice PRIMARY.
- favoritos utiliza llave primaria compuesta (id_usuario, id_producto).
- usuario_roles utiliza llave primaria compuesta (id_usuario, id_rol).
- Se confirmaron índices únicos relevantes para categorias.nombre, roles.nombre, usuarios.correo, vendedores.id_usuario, inventarios.id_producto, items_carrito(id_carrito, id_producto) y pedidos_vendedor(id_pedido, id_vendedor).
- No se confirmó una ausencia concreta de índices que deba corregirse en esta etapa.

Reglas de eliminación y actualización:
- Se revisaron las 21 relaciones y sus reglas ON DELETE y ON UPDATE.
- Todas las reglas ON UPDATE observadas son NO ACTION.
- CASCADE se utiliza en relaciones accesorias como favoritos, usuario_roles, inventarios, carritos e items de carrito, entre otras.
- RESTRICT protege relaciones importantes, por ejemplo los detalles de pedidos respecto de productos y pedidos por vendedor.
- SET NULL permite conservar historial o notificaciones cuando se elimina el registro referenciado en las relaciones correspondientes.
- La restricción detalles_pedido.id_producto hacia productos.id_producto protege el historial de compras y coincide con la regla de no eliminar productos que ya fueron comprados.
- La restricción items_carrito.id_producto puede impedir borrar un producto que todavía está en un carrito. Queda pendiente decidir la regla funcional definitiva.
- La eliminación de usuarios vendedores puede verse limitada por productos asociados. Se deberá manejar desde las reglas de negocio y no asumir que CASCADE elimina toda la información relacionada.

Valores predeterminados y nulabilidad:
- Se inspeccionaron los valores DEFAULT, IS_NULLABLE y EXTRA de las 168 columnas.
- categorias.activa, productos.activo y usuarios.activo tienen valor predeterminado true.
- inventarios.stock_fisico y stock_reservado tienen valor predeterminado cero.
- notificaciones.leida tiene valor predeterminado false.
- Las cantidades, precios y estados obligatorios no tienen todos valores predeterminados; la aplicación debe asignarlos y validarlos.
- Los campos opcionales de imágenes, video, descripciones y algunos datos de historial/notificaciones admiten NULL conforme al diseño.

INVESTIGACIÓN DE TABLAS HEREDADAS users, usuarios Y passkeys
Se revisaron las tablas y dependencias:
- users es una tabla heredada del Starter Kit y contiene campos estándar como id, name, email y password, además de columnas de verificación, 2FA y remember_token.
- usuarios es la tabla principal del sistema TecnoMercado y utiliza el modelo App\Models\Usuario.
- passkeys.user_id tiene una clave foránea hacia users.id.
- Las tablas de negocio se relacionan con usuarios, no con users.
- El modelo User heredado implementa funcionalidades de verificación, 2FA y passkeys.
- Fortify tiene habilitados solamente registro y recuperación de contraseña; verificación de correo y 2FA están deshabilitados intencionalmente.
- La vista de seguridad heredada contiene operaciones de contraseña que utilizan el campo password, incompatible con el campo contrasena del modelo Usuario.
- La funcionalidad passkeys está asociada a users y no se ha confirmado su compatibilidad con Usuario.
- DatabaseSeeder todavía importa App\Models\User y utiliza User::factory(), por lo que es inconsistente con el modelo principal.
- No se eliminó ni modificó ninguna tabla, modelo o migración heredada durante la auditoría.
- Se decidió revisar estas dependencias antes de retirar o adaptar componentes heredados.

ANOMALÍA DE LAS MIGRACIONES roles Y usuario_roles
Se identificó una inconsistencia entre el nombre y el contenido de dos migraciones:
- database/migrations/2026_11_02_230341_create_roles_table.php actualmente crea la tabla usuario_roles.
- database/migrations/2026_11_02_230342_create_usuario_roles_table.php actualmente crea la tabla roles.
Por tanto, los contenidos actuales están intercambiados respecto a los nombres de archivo.

El historial de la tabla migrations mostró:
- 2026_11_02_224604_create_usuarios_table: batch 1.
- 2026_11_02_230341_create_roles_table: batch 2.
- 2026_11_02_230342_create_usuario_roles_table: batch 3.
- Las otras migraciones de negocio aparecen en batch 3.

Git mostró para ambos archivos únicamente el commit:
74354d9 feat: agregar migraciones de la base de datos
Fecha del commit: 2026-11-05 09:17:23 -0600.

MySQL informó:
- Tabla roles creada el 2026-11-02 16:57:45.
- Tabla usuario_roles creada el 2026-11-03 09:42:46.

Conclusión:
La evidencia es compatible con que las tablas se crearan correctamente y que posteriormente los contenidos de los archivos de migración se intercambiaran antes del primer commit que los conserva. No es posible demostrar el momento exacto del intercambio con la información disponible.
No modificar migraciones ejecutadas ni ejecutar migrate:fresh. Cualquier corrección futura debe hacerse mediante una estrategia segura y documentada para migraciones ya ejecutadas.

RESULTADO Y CIERRE DE LA ETAPA 1
La auditoría de base de datos quedó cerrada como diagnóstico.
La estructura principal está presente y cubre los módulos previstos. No se encontró motivo para reconstruir la base desde cero.
Se documentaron los riesgos y pendientes:
- Inconsistencia de contenido/nombre de migraciones roles y usuario_roles.
- Coexistencia de users y usuarios, con passkeys apuntando a users.
- Seeder y modelo heredados que deben revisarse.
- Ausencia de CHECK para validar cantidades, precios, stocks y otros límites.
- Decisión pendiente sobre carrito activo único por usuario.
- Decisión pendiente sobre eliminación de productos presentes en carritos.
- Revisar índices según consultas reales durante optimización.
No se modificó la estructura ni los datos durante esta auditoría.

ESTADO ACTUAL Y PUNTO EXACTO DE CONTINUACIÓN

ETAPA 1. Auditoría de base de datos:
CERRADA como diagnóstico. Las anomalías y decisiones pendientes quedaron documentadas.

ETAPA 2. Auditoría de modelos Eloquent:
INICIADA, pero todavía no se ha revisado el contenido de los modelos en esta etapa.

Actividad 2.1: modelos de usuarios y roles.
Pendiente de ejecutar y analizar el siguiente bloque de lectura:
- app/Models/Usuario.php
- app/Models/Rol.php
- app/Models/UsuarioRol.php
- app/Models/User.php

El objetivo será revisar tabla, llave primaria, autoincremento, fillable/guarded, casts, atributos, métodos, relaciones y compatibilidad con las columnas reales.
Después se continuará con los demás modelos, sin adelantarse a la auditoría de relaciones ni modificar código durante el diagnóstico.

LISTADO DE MODELOS ENCONTRADOS
Carrito.php
Categoria.php
DetallePedido.php
Favorito.php
HistorialEstadoPedido.php
Inventario.php
Notificacion.php
Pedido.php
PedidoVendedor.php
Producto.php
Rol.php
User.php
Usuario.php
UsuarioRol.php
Vendedor.php

Se observó que existe el modelo User heredado y no aparece un modelo ItemCarrito, a pesar de que existe la tabla items_carrito. Esto debe investigarse durante la auditoría de modelos; todavía no se debe asumir que sea un error.

INSTRUCCIONES PARA CONTINUAR
1. Comenzar indicando expresamente: “Actividad 2.1 — Auditoría de modelos de usuarios y roles”.
2. No repetir consultas de base de datos ya ejecutadas, salvo que una inconsistencia concreta requiera evidencia nueva.
3. Solicitar únicamente la lectura de los cuatro modelos indicados en la actividad 2.1.
4. Esperar la salida del usuario antes de analizar o solicitar el siguiente bloque.
5. No modificar archivos ni base de datos durante la auditoría.
6. No crear commits ni hacer push sin pruebas y autorización.
7. Registrar cada hallazgo, su evidencia, su clasificación y la etapa en la que debe resolverse.
8. No adelantar dashboard ni interfaces; primero terminar el backend conforme a la hoja de ruta.

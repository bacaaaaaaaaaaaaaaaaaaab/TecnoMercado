# TecnoMercado --- Configuración del entorno de desarrollo

## 1. Introducción

Este documento registra paso a paso la configuración inicial del entorno
de desarrollo del proyecto **TecnoMercado**, una aplicación web
desarrollada con **Python, Django y MySQL**, utilizando Git/GitHub y
WebStorm.

El objetivo de esta etapa fue dejar preparado el entorno para comenzar
posteriormente con la estructura de la base de datos y el desarrollo de
las aplicaciones de Django.

> **Nota:** Las contraseñas no se incluyen en este documento por
> seguridad. En los ejemplos se utiliza `<CONTRASEÑA_MYSQL>`.

------------------------------------------------------------------------

# 2. Tecnologías utilizadas

-   Python 3.13.5
-   Django 6.1.1
-   MySQL Server
-   MySQL Connector para Python mediante `mysqlclient`
-   Git 2.45.1
-   GitHub
-   WebStorm
-   Windows 11
-   Entorno virtual de Python (`venv`)

------------------------------------------------------------------------

# 3. Verificación inicial de Python

Primero se comprobó la versión de Python instalada:

``` powershell
py --version
```

Resultado:

``` text
Python 3.13.5
```

Inicialmente, el comando:

``` powershell
python
```

no funcionaba correctamente desde Windows debido a los alias de
ejecución de Windows/Windows Store.

Por eso se utilizó:

``` powershell
py
```

para crear el entorno virtual.

------------------------------------------------------------------------

# 4. Verificación de Git

Se comprobó que Git estuviera instalado:

``` powershell
git --version
```

Resultado:

``` text
git version 2.45.1.windows.1
```

Git será utilizado para controlar las versiones del proyecto y
posteriormente sincronizarlo con GitHub.

------------------------------------------------------------------------

# 5. Creación del proyecto

Se trabajó en la siguiente carpeta:

``` text
C:\Proyectos\TecnoMercado
```

La terminal se ubicó en esa carpeta:

``` powershell
cd C:\Proyectos\TecnoMercado
```

------------------------------------------------------------------------

# 6. Creación del entorno virtual

Se creó un entorno virtual para mantener aisladas las dependencias de
Python del proyecto:

``` powershell
py -m venv venv
```

Esto creó la carpeta:

``` text
C:\Proyectos\TecnoMercado\venv
```

## ¿Por qué usamos un entorno virtual?

El entorno virtual permite instalar Django y las demás librerías
únicamente para TecnoMercado, evitando mezclar las dependencias con
otros proyectos de Python.

------------------------------------------------------------------------

# 7. Problema al activar el entorno virtual

Al intentar activar el entorno virtual en PowerShell podía aparecer un
problema relacionado con la política de ejecución de scripts.

Se solucionó cambiando la política únicamente para el usuario actual:

``` powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

Después se activó el entorno:

``` powershell
.\venv\Scripts\Activate.ps1
```

Cuando quedó correctamente activado, la terminal mostró:

``` text
(venv) PS C:\Proyectos\TecnoMercado>
```

A partir de ese momento, los comandos de Python y pip se ejecutaron
dentro del entorno virtual.

------------------------------------------------------------------------

# 8. Comprobación de pip

Dentro del entorno virtual se comprobó pip:

``` powershell
python -m pip --version
```

Resultado registrado:

``` text
pip 25.1.1
```

Se utilizó `python -m pip` en lugar de depender directamente del comando
`pip`, porque así se asegura que pip pertenece al Python del entorno
virtual activo.

------------------------------------------------------------------------

# 9. Instalación de Django

Se instaló Django dentro del entorno virtual:

``` powershell
python -m pip install django
```

La versión instalada fue:

``` text
Django 6.1.1
```

Se puede comprobar posteriormente con:

``` powershell
python -m django --version
```

------------------------------------------------------------------------

# 10. Creación del proyecto Django

Se creó el proyecto Django con:

``` powershell
django-admin startproject config .
```

El punto final `.` indica que Django debe crear el proyecto en la
carpeta actual, en lugar de crear otra carpeta adicional.

La estructura inicial quedó aproximadamente así:

``` text
TecnoMercado/
│
├── config/
│   ├── __init__.py
│   ├── settings.py
│   ├── urls.py
│   ├── asgi.py
│   └── wsgi.py
│
├── venv/
├── manage.py
└── db.sqlite3
```

------------------------------------------------------------------------

# 11. Error al ejecutar `startproject` nuevamente

Posteriormente se ejecutó accidentalmente otra vez:

``` powershell
django-admin startproject config .
```

Django respondió:

``` text
CommandError: C:\Proyectos\TecnoMercado\manage.py already exists.
```

## ¿Qué significa?

Django detectó que ya existía un proyecto en esa carpeta porque
`manage.py` ya estaba creado.

No fue necesario volver a crear el proyecto. El proyecto existente
continuó funcionando correctamente.

------------------------------------------------------------------------

# 12. Migraciones iniciales con SQLite

Antes de configurar MySQL se ejecutaron las migraciones iniciales:

``` powershell
python manage.py migrate
```

Django creó correctamente las tablas necesarias para:

-   usuarios/autenticación,
-   administración,
-   sesiones,
-   content types,
-   permisos.

También se generó inicialmente:

``` text
db.sqlite3
```

Esto permitió comprobar que Django estaba funcionando antes de cambiar
la base de datos a MySQL.

------------------------------------------------------------------------

# 13. Comprobación del proyecto Django

Se ejecutó:

``` powershell
python manage.py check
```

Resultado:

``` text
System check identified no issues (0 silenced).
```

Esto confirmó que, en ese momento, la configuración de Django no
presentaba problemas.

------------------------------------------------------------------------

# 14. Prueba del servidor Django

Se inició el servidor:

``` powershell
python manage.py runserver
```

Django quedó disponible localmente en:

``` text
http://127.0.0.1:8000/
```

Esto permitió comprobar que el proyecto Django había sido creado
correctamente.

Para detener el servidor:

``` text
Ctrl + C
```

------------------------------------------------------------------------

# 15. Comprobación de MySQL

Se comprobó que MySQL estuviera instalado en Windows.

El servicio encontrado fue:

``` text
MySQL80
```

Estado:

``` text
Running
```

Esto confirmó que el servidor MySQL 8.0 estaba funcionando.

------------------------------------------------------------------------

# 16. Localización de MySQL

Se localizaron los ejecutables de MySQL.

Uno de los principales fue:

``` text
C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe
```

También se encontró una copia asociada a MySQL Workbench.

------------------------------------------------------------------------

# 17. Agregar MySQL al PATH

Para poder utilizar `mysql` directamente desde PowerShell, se agregó al
PATH del usuario:

``` text
C:\Program Files\MySQL\MySQL Server 8.0\bin
```

Después de abrir una nueva terminal se comprobó:

``` powershell
mysql --version
```

Resultado:

``` text
mysql  Ver 8.0.45 for Win64 on x86_64
```

------------------------------------------------------------------------

# 18. Conexión a MySQL

Se probó la conexión utilizando:

``` powershell
mysql -u root -p
```

Después se introdujo la contraseña de `root`.

La conexión fue exitosa y apareció el monitor de MySQL.

> La contraseña utilizada no se documenta aquí.

------------------------------------------------------------------------

# 19. Creación de la base de datos de TecnoMercado

Dentro de MySQL se ejecutó:

``` sql
CREATE DATABASE tecnomercado_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Esto creó la base de datos:

``` text
tecnomercado_db
```

Después se comprobó:

``` sql
SHOW DATABASES;
```

Entre las bases disponibles aparecieron:

``` text
tecnomercado_db
empresa
sakila
world
```

Las bases `empresa`, `sakila` y `world` ya existían en la instalación de
MySQL.

------------------------------------------------------------------------

# 20. Instalación del controlador MySQL para Python

Para que Django pudiera comunicarse con MySQL se instaló `mysqlclient`
dentro del entorno virtual:

``` powershell
python -m pip install mysqlclient
```

Resultado:

``` text
Successfully installed mysqlclient-2.3.0
```

Este paquete permite que Django utilice el backend de MySQL desde
Python.

------------------------------------------------------------------------

# 21. Configuración de Django para utilizar MySQL

Se abrió:

``` text
config/settings.py
```

La configuración original utilizaba SQLite.

Se cambió `DATABASES` por una configuración de MySQL similar a:

``` python
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'tecnomercado_db',
        'USER': 'root',
        'PASSWORD': '<CONTRASEÑA_MYSQL>',
        'HOST': 'localhost',
        'PORT': '3306',
    }
}
```

## Explicación

  Parámetro    Función
  ------------ -----------------------------------
  `ENGINE`     Indica que Django utilizará MySQL
  `NAME`       Nombre de la base de datos
  `USER`       Usuario de MySQL
  `PASSWORD`   Contraseña del usuario
  `HOST`       Servidor donde está MySQL
  `PORT`       Puerto utilizado por MySQL

------------------------------------------------------------------------

# 22. Primer error importante: incompatibilidad Django/MySQL

Después de cambiar Django para utilizar MySQL se ejecutó:

``` powershell
python manage.py check
```

Apareció el siguiente error:

``` text
django.db.utils.NotSupportedError:
MySQL 8.4 or later is required (found 8.0.45).
```

## ¿Qué estaba pasando?

El problema no era la contraseña ni `mysqlclient`.

La configuración tenía:

``` text
Django 6.1.1
MySQL 8.0.45
```

La versión de Django utilizada requiere una versión más reciente de
MySQL.

Por esta razón se decidió actualizar MySQL de:

``` text
MySQL 8.0.45
```

a:

``` text
MySQL 8.4
```

------------------------------------------------------------------------

# 23. Intento de actualización mediante MySQL Installer

Se abrió:

``` text
MySQL Installer
```

Primero se actualizó el catálogo de productos mediante:

``` text
Catalog
```

y posteriormente:

``` text
Execute
```

Sin embargo, el instalador existente correspondía a la línea de MySQL
8.0 y no realizó directamente la actualización del servidor 8.0 a 8.4.

Por esta razón se descargó el instalador específico de MySQL 8.4.

------------------------------------------------------------------------

# 24. Descarga de MySQL 8.4

Se descargó el archivo:

``` text
mysql-8.4.11-winx64.msi
```

Este es el instalador MSI de MySQL Server 8.4 para Windows.

------------------------------------------------------------------------

# 25. Instalación de MySQL Server 8.4

En la pantalla:

``` text
MySQL Server 8.4 Setup
Choose Setup Type
```

se seleccionó:

``` text
Typical
```

La opción Typical instala los componentes habituales necesarios para
utilizar MySQL Server.

------------------------------------------------------------------------

# 26. MySQL Configurator

Después de la instalación apareció:

``` text
MySQL Configurator
MySQL Server 8.4.11
```

El configurador mostró diferentes etapas:

``` text
Welcome
MySQL Server Installations
Type and Networking
Accounts and Roles
Windows Service
Server File Permissions
Logging Options
Advanced Options
Sample Databases
Apply Configuration
Configuration Complete
```

------------------------------------------------------------------------

# 27. Detección de la instalación existente

MySQL Configurator detectó:

``` text
An existing MySQL Server installation was found on your system.
```

Se ofrecieron dos posibilidades:

1.  Actualización en el mismo lugar (*in-place upgrade*).
2.  Instalación independiente (*side-by-side*).

Se eligió:

``` text
Perform an in-place upgrade of the existing MySQL Server installation
```

Esto es importante porque queremos actualizar la instalación existente
en lugar de crear un segundo servidor independiente.

------------------------------------------------------------------------

# 28. Comprobación de la instalación MySQL 8.0.45

El configurador detectó correctamente:

``` text
Version:
8.0.45
```

También detectó las rutas de la instalación:

``` text
Install Directory:
C:\Program Files\MySQL\MySQL Server 8.0
```

``` text
Data Directory:
C:\ProgramData\MySQL\MySQL Server 8.0\Data
```

Y la configuración:

``` text
C:\ProgramData\MySQL\MySQL Server 8.0\my.ini
```

El puerto detectado fue:

``` text
3306
```

Se utilizó la contraseña actual de `root` para conectar con la
instalación existente.

> La contraseña no se incluye en este documento.

La conexión fue aceptada y el configurador identificó correctamente el
servidor 8.0.45.

------------------------------------------------------------------------

# 29. Respaldo antes de actualizar

Antes de realizar la actualización apareció la pantalla:

``` text
Backup Data
```

con dos opciones:

``` text
Run a mysqldump backup prior to upgrade
```

y:

``` text
No thanks, I have already run a backup
```

Se seleccionó:

``` text
Run a mysqldump backup prior to upgrade
```

La finalidad es generar un respaldo de las bases de datos antes de
modificar la instalación.

Esto es especialmente importante porque además de `tecnomercado_db`
existen otras bases en la instalación de MySQL.

------------------------------------------------------------------------

# 30. Estado actual del proceso

En este punto, la actualización todavía **no se debe considerar
terminada**.

El proceso llegó a la pantalla de respaldo:

``` text
Backup Data
```

y se seleccionó la opción:

``` text
Run a mysqldump backup prior to upgrade
```

El siguiente paso consiste en continuar con el configurador y completar
la actualización a MySQL 8.4.11.

Después de completar la actualización se debe comprobar la versión:

``` powershell
mysql --version
```

El objetivo es obtener una versión de la serie:

``` text
8.4.x
```

También se deberá comprobar que el servicio MySQL continúe funcionando.

------------------------------------------------------------------------

# 31. Comprobaciones posteriores a la actualización

Una vez terminada la actualización, se recomienda comprobar:

### Versión de MySQL

``` powershell
mysql --version
```

### Servicio MySQL

``` powershell
Get-Service *mysql*
```

### Conexión

``` powershell
mysql -u root -p
```

### Bases de datos

Dentro de MySQL:

``` sql
SHOW DATABASES;
```

Se debe comprobar que continúe apareciendo:

``` text
tecnomercado_db
```

------------------------------------------------------------------------

# 32. Volver a comprobar Django

Con MySQL 8.4 funcionando, desde:

``` text
C:\Proyectos\TecnoMercado
```

y con el entorno virtual activo:

``` text
(venv)
```

se debe ejecutar:

``` powershell
python manage.py check
```

El objetivo es que desaparezca el error:

``` text
MySQL 8.4 or later is required (found 8.0.45)
```

Posteriormente se podrá ejecutar:

``` powershell
python manage.py migrate
```

para crear las tablas de Django dentro de:

``` text
tecnomercado_db
```

------------------------------------------------------------------------

# 33. Comandos principales utilizados

A continuación se concentra la lista de comandos utilizados durante la
configuración:

## Python

``` powershell
py --version
```

``` powershell
py -m venv venv
```

``` powershell
.\venv\Scripts\Activate.ps1
```

``` powershell
python -m pip --version
```

``` powershell
python -m pip install django
```

``` powershell
python -m django --version
```

## Django

``` powershell
django-admin startproject config .
```

``` powershell
python manage.py migrate
```

``` powershell
python manage.py check
```

``` powershell
python manage.py runserver
```

## Git

``` powershell
git --version
```

## MySQL

``` powershell
mysql --version
```

``` powershell
mysql -u root -p
```

## Instalación del controlador

``` powershell
python -m pip install mysqlclient
```

## Verificación del servicio MySQL

``` powershell
Get-Service *mysql*
```

------------------------------------------------------------------------

# 34. Errores encontrados y soluciones

## Error 1 --- Política de ejecución de PowerShell

### Problema

No se podía activar correctamente el entorno virtual mediante el script
de PowerShell.

### Solución

``` powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

Después:

``` powershell
.\venv\Scripts\Activate.ps1
```

------------------------------------------------------------------------

## Error 2 --- Ejecutar `startproject` dos veces

### Error

``` text
CommandError:
C:\Proyectos\TecnoMercado\manage.py already exists.
```

### Causa

El proyecto Django ya había sido creado.

### Solución

No volver a ejecutar `startproject`. Se continuó utilizando el proyecto
existente.

------------------------------------------------------------------------

## Error 3 --- Incompatibilidad de Django con MySQL

### Error

``` text
django.db.utils.NotSupportedError:
MySQL 8.4 or later is required (found 8.0.45).
```

### Causa

La versión de Django instalada era:

``` text
Django 6.1.1
```

mientras que MySQL era:

``` text
MySQL 8.0.45
```

### Solución elegida

Actualizar MySQL a la serie:

``` text
MySQL 8.4
```

Se inició el proceso de actualización mediante:

``` text
mysql-8.4.11-winx64.msi
```

y posteriormente MySQL Configurator.

------------------------------------------------------------------------

# 35. Estructura actual del proyecto

La estructura inicial del proyecto quedó:

``` text
C:\Proyectos\TecnoMercado
│
├── config/
│   ├── __init__.py
│   ├── settings.py
│   ├── urls.py
│   ├── asgi.py
│   └── wsgi.py
│
├── venv/
│
├── manage.py
│
└── db.sqlite3
```

> `db.sqlite3` corresponde a la etapa inicial en la que Django fue
> probado con SQLite. No debe eliminarse todavía hasta confirmar que la
> conexión y las migraciones con MySQL funcionan correctamente.

------------------------------------------------------------------------

# 36. Próximos pasos

Después de terminar la actualización de MySQL, la secuencia recomendada
para continuar el proyecto es:

1.  Confirmar que MySQL quedó en 8.4.x.
2.  Confirmar que el servicio MySQL está funcionando.
3.  Confirmar que `tecnomercado_db` sigue existiendo.
4.  Ejecutar:

``` powershell
python manage.py check
```

5.  Ejecutar las migraciones utilizando MySQL:

``` powershell
python manage.py migrate
```

6.  Comprobar las tablas creadas en `tecnomercado_db`.
7.  Crear las aplicaciones Django del proyecto.
8.  Diseñar los modelos de la base de datos.
9.  Ejecutar nuevas migraciones conforme se creen los modelos.
10. Configurar autenticación y permisos.
11. Comenzar el desarrollo de las funcionalidades de TecnoMercado.

------------------------------------------------------------------------

# 37. Conclusión

Durante esta etapa se preparó el entorno base de TecnoMercado:

-   Se verificó Python.
-   Se creó un entorno virtual.
-   Se instaló Django.
-   Se creó el proyecto Django.
-   Se probaron las migraciones iniciales.
-   Se verificó el servidor Django.
-   Se instaló y verificó MySQL.
-   Se creó `tecnomercado_db`.
-   Se instaló `mysqlclient`.
-   Se configuró Django para utilizar MySQL.
-   Se detectó la incompatibilidad entre Django 6.1.1 y MySQL 8.0.45.
-   Se inició la actualización de MySQL 8.0.45 a MySQL 8.4.11.
-   Se seleccionó una actualización *in-place*.
-   Se inició el respaldo mediante `mysqldump` antes de realizar la
    actualización.

El siguiente objetivo es completar la actualización y verificar la
comunicación:

``` text
Django 6.1.1
      ↓
mysqlclient
      ↓
MySQL 8.4.11
      ↓
tecnomercado_db
```

Una vez comprobada esta conexión, el proyecto estará listo para comenzar
con el diseño de los modelos y la estructura de la base de datos de
TecnoMercado.

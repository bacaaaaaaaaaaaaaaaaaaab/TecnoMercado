# TecnoMercado - Documentación de configuración del entorno

**Fecha:** 28 de septiembre de 2026  
**Propósito:** documentar paso a paso la preparación del entorno de desarrollo antes de comenzar la implementación funcional de TecnoMercado.

> **Nota de seguridad:** la contraseña real de MySQL se omite de este documento. Debe mantenerse en `.env` y nunca publicarse en GitHub.

## 1. Tecnologías configuradas

| Tecnología | Versión / configuración | Estado |
|---|---|---|
| Python | 3.13.5 | ✅ Comprobado |
| Django | 6.1.1 | ✅ Comprobado |
| MySQL Server | 8.4.11, actualizado desde 8.0.45 | ✅ Comprobado |
| mysqlclient | 2.3.0 | ✅ Instalado |
| python-dotenv | 1.2.3 | ✅ Instalado |
| Git | 2.45.1.windows.1 | ✅ Configurado |
| GitHub | Repositorio remoto TecnoMercado | ✅ Conectado |

## 2. Arquitectura del entorno

```text
Navegador / desarrollo local
        |
        v
Django 6.1.1
        |
        +--> Django ORM
        |       |
        |       v
        |   mysqlclient 2.3.0
        |       |
        |       v
        |   MySQL Server 8.4.11
        |       |
        |       v
        |   tecnomercado_db
        |
        +--> Authentication / sessions / admin

Código del proyecto
        |
        v
      Git
        |
        v
     GitHub
```

---

## 3. Actualización de MySQL Server

Se actualizó **MySQL Server 8.0.45 -> 8.4.11** mediante MySQL Configurator.

El configurador mostró las etapas de respaldo, permisos de archivos, aplicación de la configuración y finalización.

El directorio de datos pasó de:

```text
C:\ProgramData\MySQL\MySQL Server 8.0
```

a:

```text
C:\ProgramData\MySQL\MySQL Server 8.4
```

En **Server File Permissions** se utilizó la opción para otorgar acceso al usuario que ejecuta el servicio de Windows y al grupo de administradores.

En **Apply Configuration** se ejecutó el proceso completo. El instalador incluyó acciones como respaldar la base de datos, detener la instancia anterior, renombrar el directorio de datos, escribir la configuración, actualizar permisos, ajustar el servicio, iniciar MySQL 8.4.11 y actualizar las tablas del sistema.

El instalador terminó con:

```text
Configuration Complete
```

---

## 4. Comprobación de MySQL

### 4.1. Error: `mysql` no se reconoce como comando

Al ejecutar inicialmente:

```powershell
mysql --version
```

PowerShell indicó que `mysql` no era reconocido como comando.

Se comprobó la instalación usando la ruta completa:

```powershell
"C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe" --version
```

El resultado fue:

```text
Ver 8.4.11 for Win64 on x86_64 (MySQL Community Server - GPL)
```

La causa era que la carpeta `bin` de MySQL no estaba en el **PATH** de Windows.

Se agregó:

```text
C:\Program Files\MySQL\MySQL Server 8.4\bin
```

Después de cerrar y abrir una nueva consola, el comando volvió a funcionar directamente:

```powershell
mysql --version
```

### 4.2. Comprobar el servicio

```powershell
sc query MySQL84
```

Resultado importante:

```text
ESTADO : 4 RUNNING
```

Esto confirmó que MySQL 8.4.11 estaba ejecutándose como servicio de Windows.

### 4.3. Comprobar acceso y bases de datos

```powershell
mysql -u root -p
```

Dentro de MySQL:

```sql
SHOW DATABASES;
```

Entre las bases mostradas estaba:

```text
tecnomercado_db
```

Por lo tanto, la actualización no eliminó la base de datos de TecnoMercado.

---

## 5. Comprobación del entorno Django

Se trabajó desde:

```text
C:\Proyectos\TecnoMercado
```

Se activó el entorno virtual:

```powershell
cd C:\Proyectos\TecnoMercado
.\venv\Scripts\Activate.ps1
```

### 5.1. Comprobar `mysqlclient`

```powershell
pip show mysqlclient
```

Se confirmó:

```text
Version: 2.3.0
```

### 5.2. Comprobar Django

```powershell
python manage.py check
```

Resultado:

```text
System check identified no issues (0 silenced).
```

---

## 6. Migraciones de Django y Authentication System

Se ejecutó:

```powershell
python manage.py migrate
```

Las migraciones base terminaron correctamente con `OK` para:

- `admin`
- `auth`
- `contenttypes`
- `sessions`

Luego se verificaron con:

```powershell
python manage.py showmigrations
```

Las migraciones mostradas aparecieron como `[X]`, indicando que estaban aplicadas.

> En esta etapa no se crearon todavía modelos propios de TecnoMercado. El objetivo era configurar el entorno, no comenzar la implementación funcional.

---

## 7. Verificación real de Django -> MySQL

La configuración utilizada fue conceptualmente:

```python
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'tecnomercado_db',
        'USER': 'root',
        'PASSWORD': '<CONTRASEÑA_EN_.env>',
        'HOST': 'localhost',
        'PORT': '3306',
    }
}
```

Se abrió el shell:

```powershell
python manage.py shell
```

Y se ejecutó:

```python
from django.db import connection
connection.settings_dict
connection.ensure_connection()
print(connection.is_usable())
```

Resultado:

```text
True
```

Esto confirmó que Django podía conectarse y utilizar la base de datos MySQL.

---

## 8. Servidor de desarrollo

Se inició con:

```powershell
python manage.py runserver
```

Resultado comprobado:

```text
System check identified no issues (0 silenced).
Django version 6.1.1
Starting WSGI development server at http://127.0.0.1:8000/
```

El aviso de que `runserver` es un servidor de desarrollo no es un error; indica que no debe utilizarse como servidor de producción.

---

## 9. Proteger la contraseña con `.env`

Antes de enviar el proyecto a GitHub se instaló `python-dotenv`:

```powershell
pip install python-dotenv
```

Versión instalada:

```text
1.2.3
```

Se creó `.env` en la raíz del proyecto con valores similares a:

```env
DB_NAME=tecnomercado_db
DB_USER=root
DB_PASSWORD=<CONTRASEÑA_REAL>
DB_HOST=localhost
DB_PORT=3306
```

La contraseña real debe permanecer solo en `.env`.

### `.gitignore`

Se configuró para ignorar, entre otros:

```gitignore
venv/
.env
__pycache__/
*.py[cod]
*.pyo
db.sqlite3
*.log
.idea/
.vscode/
.DS_Store
Thumbs.db
```

---

## 10. Git

Se comprobó la instalación:

```powershell
git --version
```

Resultado:

```text
git version 2.45.1.windows.1
```

Se configuró la identidad:

```powershell
git config --global user.name "Bacab"
git config --global user.email "<CORREO_DE_GITHUB>"
```

---

## 11. Error con `.gitignore`

En el primer intento, `.gitignore` fue creado como **carpeta**. Git mostró:

```text
warning: unable to access '.gitignore': Permission denied
```

PowerShell confirmó que era un directorio:

```text
Attributes : Directory
Mode       : d-----
```

### Solución

```powershell
Remove-Item .gitignore -Recurse
New-Item .gitignore -ItemType File
```

Después se confirmó:

```text
Attributes : Archive
Mode       : -a----
```

Git volvió a reconocer correctamente el archivo.

---

## 12. Primer repositorio Git

Se cambió la rama a `main`:

```powershell
git branch -M main
```

Se prepararon los archivos:

```powershell
git add .
```

Se revisó el estado:

```powershell
git status
```

Los archivos preparados fueron principalmente:

```text
.gitignore
config/
manage.py
```

No aparecieron `.env`, `venv/`, `.idea/` ni `db.sqlite3`.

### 12.1. `git log` antes del primer commit

Al ejecutar:

```powershell
git log --oneline
```

apareció:

```text
fatal: your current branch 'main' does not have any commits yet
```

Era normal porque todavía no existía ningún commit.

### 12.2. Primer commit

```powershell
git commit -m "Configuración inicial de TecnoMercado"
```

Se creó el commit raíz:

```text
bbf000c Configuración inicial de TecnoMercado
```

---

## 13. GitHub

Se creó un repositorio remoto llamado `TecnoMercado`.

### 13.1. GitHub CLI no instalado

Se probó:

```powershell
gh --version
```

Y PowerShell indicó que `gh` no era reconocido.

Esto significa que **GitHub CLI no estaba instalado**. No fue necesario instalarlo porque Git puede conectarse directamente a GitHub mediante HTTPS.

### 13.2. Agregar el remoto

```powershell
git remote add origin https://github.com/<USUARIO>/TecnoMercado.git
```

Se comprobó con:

```powershell
git remote -v
```

El remoto `origin` quedó configurado para `fetch` y `push`.

### 13.3. Subir el primer commit

```powershell
git push -u origin main
```

Durante el proceso apareció una ventana de autenticación de GitHub. Se inició sesión mediante navegador y se autorizó el acceso.

---

## 14. Verificación final Git + GitHub

```powershell
git remote -v
git status
```

El resultado final fue:

```text
Your branch is up to date with 'origin/main'.
nothing to commit, working tree clean
```

Esto confirma que el repositorio local y GitHub están sincronizados.

---

## 15. Errores encontrados y soluciones

| Problema | Causa | Solución |
|---|---|---|
| `mysql` no se reconoce | MySQL no estaba en PATH | Agregar `C:\Program Files\MySQL\MySQL Server 8.4\bin` al PATH y abrir una nueva consola |
| `.gitignore: Permission denied` | `.gitignore` era una carpeta | Eliminar la carpeta y crear un archivo con `New-Item .gitignore -ItemType File` |
| `git log` sin commits | Todavía no había historial | Crear el primer commit |
| `gh` no se reconoce | GitHub CLI no estaba instalado | Continuar con Git + GitHub HTTPS; `gh` no era necesario |
| Aviso de actualización de pip | Había una versión más nueva disponible | Se ignoró porque no impedía continuar |

---

## 16. Estado final del entorno

- ✅ Python 3.13.5
- ✅ Django 6.1.1
- ✅ MySQL Server 8.4.11
- ✅ `mysqlclient` 2.3.0
- ✅ `python-dotenv` 1.2.3
- ✅ Django Authentication base (`auth`, `sessions`, `admin`, `contenttypes`)
- ✅ Base `tecnomercado_db` disponible
- ✅ Conexión Django -> MySQL comprobada con `True`
- ✅ Git 2.45.1 configurado
- ✅ Rama `main`
- ✅ Primer commit creado
- ✅ Repositorio GitHub conectado
- ✅ `git status` limpio y actualizado con `origin/main`
- ✅ `.env` excluido de Git mediante `.gitignore`

## 17. Comandos de referencia

### MySQL

```powershell
mysql --version
sc query MySQL84
mysql -u root -p
```

Dentro de MySQL:

```sql
SHOW DATABASES;
```

### Django

```powershell
cd C:\Proyectos\TecnoMercado
.\venv\Scripts\Activate.ps1
pip show mysqlclient
python manage.py check
python manage.py migrate
python manage.py showmigrations
python manage.py shell
python manage.py runserver
```

### Variables de entorno

```powershell
pip install python-dotenv
New-Item .env -ItemType File
```

### Git

```powershell
git --version
git config --global user.name "Bacab"
git config --global user.email "<CORREO_DE_GITHUB>"
git branch -M main
git add .
git status
git commit -m "Configuración inicial de TecnoMercado"
git log --oneline
```

### GitHub

```powershell
git remote add origin https://github.com/<USUARIO>/TecnoMercado.git
git remote -v
git push -u origin main
```

---

## 18. Siguiente etapa

Con el entorno ya configurado y comprobado, la siguiente etapa es comenzar la implementación de TecnoMercado: crear las aplicaciones Django, definir modelos, generar migraciones propias, preparar autenticación y desarrollar vistas, plantillas y CRUD de acuerdo con los requisitos del proyecto.

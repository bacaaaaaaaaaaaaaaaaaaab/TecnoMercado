# Guía y Plan de Trabajo: TecnoMercado

---

## 1. Plan de Estructuración y Orden Recomendado

Ahora que ya tienes el entorno configurado, no conviene empezar directamente a programar las vistas. Lo siguiente sería estructurar la base de datos y los modelos de Django.

### Orden recomendado:

1. **Diseñar la base de datos**
   * Identificar las entidades.
   * Definir campos.
   * Definir relaciones.
   * Decidir llaves primarias y foráneas.

2. **Crear los modelos en Django (`models.py`)**
   * Usuarios
   * Productos
   * Categorías
   * Carrito / pedidos, etc., según lo que hayan definido para el proyecto.

3. **Crear y ejecutar las migraciones**
   ```bash
   python manage.py makemigrations
   python manage.py migrate
   ```

4. **Configurar el administrador de Django (`admin.py`)**
   * Registrar modelos.
   * Comprobar que podemos crear, editar y eliminar información.

5. **Probar la base de datos**
   * Crear algunos productos de prueba.
   * Crear usuarios.
   * Comprobar relaciones.

6. **Después sí entrar a programación**
   * URLs
   * Views
   * Templates
   * Tailwind / CSS
   * Autenticación
   * CRUD de productos
   * Catálogo
   * Carrito, etc.

---

## 2. Mapa del Proyecto

```text
TecnoMercado
│
├── 1. Base de datos
│   ├── Usuarios
│   ├── Categorías
│   ├── Productos
│   └── ...
│
├── 2. Modelos Django
│
├── 3. Migraciones
│
├── 4. Admin
│
├── 5. Datos de prueba
│
└── 6. Programación de la aplicación
    ├── URLs
    ├── Views
    ├── Templates
    ├── CRUD
    ├── Autenticación
    └── Funcionalidades
```

> **Nota Importante:** Antes de escribir `models.py`, primero debemos definir exactamente qué tablas necesita TecnoMercado. Así evitamos estar modificando la base de datos a cada rato.  
> Si quieres, empezamos ahora mismo por diseñar la base de datos completa de TecnoMercado, tomando únicamente los requisitos que ya tienen para el proyecto, y después la convertimos directamente a `models.py`.

---

## 3. Verificación de Funcionamiento e Historial de Registros

¡Perfecto! 🔥 Todo está funcionando correctamente.  
De hecho, tu registro confirma que ya llegaste al panel administrativo:

```http
GET /admin/ HTTP/1.1" 200
```

Y el inicio de sesión también funcionó:

```http
POST /admin/login/?next=/admin/ HTTP/1.1" 302
GET /admin/ HTTP/1.1" 200
```

### Flujo Confirmado:
$$\text{Django} \longrightarrow \text{MySQL} \longrightarrow \text{Modelos} \longrightarrow \text{Migraciones} \longrightarrow \text{Django Admin} \longrightarrow \text{Login de administrador } \checkmark$$

> **Nota sobre el favicon:**  
> El mensaje `Not Found: /favicon.ico` no es un problema. Simplemente todavía no tenemos un favicon para el sitio.

---

## 4. Comprobación del Panel de Administración

En tu navegador, dirígete a: [http://127.0.0.1:8000/admin/](http://127.0.0.1:8000/admin/)

Deberías estar viendo el panel de Django. Busca si aparecen estas secciones:

* **PRODUCTOS**
  * Categorías
  * Inventario
  * Productos
* **PEDIDOS**
  * Artículos del carrito
  * Carritos
  * Detalles de los pedidos
  * Notificaciones
  * Pedidos

Si aparecen, ya terminamos esta etapa. 🎉

---

## 5. Próximos Pasos

No vamos a programar todavía el carrito ni las compras. El siguiente paso lógico será crear nuestras primeras categorías y productos desde Django Admin.

### Ejemplo de Categorías a Crear:
```text
Categorías
├── Microcontroladores
├── Sensores
├── Módulos
├── Cables
├── Protoboards
├── Resistencias
├── Componentes de PC
├── Herramientas
└── Libros
```

Pero antes de eso hay algo que quiero hacer bien: **configurar cómo se muestran Producto, Inventario y Pedido en el administrador**, para que no tengamos un panel lleno de información incómoda.

> **Recomendación:** Por ahora, no cambies nada en el código. Si ya estás viendo el panel `/admin/`, dime qué opciones te aparecen y seguimos desde ahí.

---

## 6. Captura de Pantalla / Texto del Navegador

```text
M correo institucional Django administration Site administration AUTHENTICATION AND AUTHORIZATION Groups Users iLovePDF | Herramie ... Gemini GearsBot + Add + Add Change Change PEDIDOS Artículos del carrito Carritos Detalles de los pedidos Notificaciones Pedidos + Add + Add + Add + Add + Add Change Change Change Change Change PRODUCTOS Categorías Inventario Productos + Add + Add + Add Change Change Change
```

---

## 7. Confirmación y Paso 1

Perfecto. ✅ Eso es exactamente lo que deberías estar viendo. Django Admin ya reconoce todos nuestros modelos y están conectados correctamente a MySQL.

Ahora vamos un paso a la vez:

### Paso 1: Comprobar que podemos crear una categoría
1. En la sección **PRODUCTOS**, haz clic en: **Categorías → + Add**
2. Te debe aparecer un formulario con los siguientes campos:
   * **Nombre**
   * **Descripción**
   * **Activo**

*Por ahora no llenes nada todavía. Solo dime si te aparece ese formulario y seguimos con el siguiente paso.*
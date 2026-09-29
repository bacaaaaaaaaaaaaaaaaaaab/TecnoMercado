# Base de Datos Inicial — TecnoMercado

## 1. Descripción

Este documento define el diseño inicial de la base de datos para **TecnoMercado**, un sistema web de comercialización de componentes electrónicos y de computadora.

La estructura está diseñada para la primera versión del proyecto y contempla:

- Gestión de usuarios mediante Django.
- Catálogo de productos.
- Categorías.
- Control de inventario.
- Carrito de compras.
- Pedidos.
- Detalles de pedidos.
- Reservación temporal de inventario.
- Notificaciones para clientes y vendedores.
- Flujo de aceptación, rechazo y finalización de pedidos.

La implementación se realizará posteriormente mediante los modelos de Django y MySQL.

---

# 2. Entidades principales

La base de datos estará compuesta por los siguientes modelos propios:

1. `Categoria`
2. `Producto`
3. `Inventario`
4. `Carrito`
5. `ItemCarrito`
6. `Pedido`
7. `DetallePedido`
8. `Notificacion`

Además, se utilizará el modelo de usuarios proporcionado por Django:

- `auth_user`

---

# 3. Modelo Usuario

TecnoMercado utilizará el sistema de autenticación de Django en lugar de crear una tabla de usuarios personalizada.

### Tabla

`auth_user`

### Campos principales

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | PK | Identificador del usuario |
| `username` | VARCHAR | Nombre de usuario |
| `password` | VARCHAR | Contraseña almacenada por Django |
| `first_name` | VARCHAR | Nombre |
| `last_name` | VARCHAR | Apellido |
| `email` | VARCHAR | Correo electrónico |
| `is_staff` | BOOLEAN | Permisos administrativos |
| `is_active` | BOOLEAN | Estado del usuario |
| `date_joined` | DATETIME | Fecha de registro |

---

# 4. Modelo Categoria

Permite clasificar los productos disponibles en TecnoMercado.

### Tabla

`categoria`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | PK | Identificador |
| `nombre` | VARCHAR(100) | Nombre de la categoría |
| `descripcion` | TEXT | Descripción |
| `activo` | BOOLEAN | Indica si la categoría está disponible |

### Categorías iniciales propuestas

- Microcontroladores
- Sensores
- Módulos electrónicos
- Cables
- Protoboards
- Resistencias
- Componentes para PC
- Herramientas
- Libros

### Relación

```text
Categoria 1 ───────── N Producto
```

Una categoría puede tener muchos productos y cada producto pertenece a una categoría.

---

# 5. Modelo Producto

Representa los productos que se muestran en el catálogo.

### Tabla

`producto`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | PK | Identificador |
| `categoria_id` | FK | Categoría a la que pertenece |
| `nombre` | VARCHAR(150) | Nombre del producto |
| `descripcion` | TEXT | Descripción del producto |
| `marca` | VARCHAR(100) | Marca |
| `modelo` | VARCHAR(100) | Modelo |
| `sku` | VARCHAR(50) | Código único del producto |
| `precio` | DECIMAL(10,2) | Precio actual |
| `imagen` | VARCHAR | Ruta o referencia de la imagen |
| `activo` | BOOLEAN | Indica si está disponible |
| `fecha_creacion` | DATETIME | Fecha de creación |
| `fecha_actualizacion` | DATETIME | Última actualización |

---

# 6. Modelo Inventario

Controla las existencias de cada producto.

### Tabla

`inventario`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | PK | Identificador |
| `producto_id` | OneToOne | Producto relacionado |
| `stock_fisico` | INT | Cantidad física disponible en inventario |
| `stock_reservado` | INT | Cantidad apartada por pedidos activos |
| `stock_minimo` | INT | Nivel mínimo de inventario |
| `actualizado` | DATETIME | Última actualización |

## Stock disponible

El stock disponible no se almacenará directamente. Se calculará mediante:

```text
stock_disponible = stock_fisico - stock_reservado
```

### Ejemplo

```text
Stock físico:       20
Stock reservado:    5
Stock disponible:  15
```

Esto permite reservar productos cuando un cliente confirma un pedido sin considerar inmediatamente esas unidades como vendidas.

---

# 7. Modelo Carrito

Representa el carrito de compras de cada usuario.

### Tabla

`carrito`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | PK | Identificador |
| `usuario_id` | OneToOne | Usuario propietario |
| `fecha_creacion` | DATETIME | Fecha de creación |
| `fecha_actualizacion` | DATETIME | Última actualización |

### Relación

```text
Usuario 1 ───────── 1 Carrito
```

Cada usuario tendrá un carrito asociado.

---

# 8. Modelo ItemCarrito

Representa cada producto agregado al carrito.

### Tabla

`item_carrito`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | PK | Identificador |
| `carrito_id` | FK | Carrito al que pertenece |
| `producto_id` | FK | Producto agregado |
| `cantidad` | INT | Cantidad solicitada |

### Ejemplo

```text
Carrito de un usuario

ESP32 DevKit V1     × 2
Sensor HC-SR04      × 3
Protoboard          × 1
```

---

# 9. Modelo Pedido

Registra la solicitud de compra realizada por el cliente.

### Tabla

`pedido`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | PK | Identificador del pedido |
| `usuario_id` | FK | Cliente que realizó el pedido |
| `estado` | VARCHAR | Estado actual del pedido |
| `total` | DECIMAL(10,2) | Total del pedido |
| `fecha_creacion` | DATETIME | Fecha de creación |
| `fecha_actualizacion` | DATETIME | Última actualización |
| `fecha_completado` | DATETIME | Fecha en que se completó |
| `motivo_cancelacion` | TEXT | Motivo del rechazo o cancelación |

## Estados del pedido

```text
PENDIENTE
ACEPTADO
EN_PROCESO
COMPLETADO
CANCELADO
```

### Flujo normal

```text
PENDIENTE
    ↓
ACEPTADO
    ↓
EN_PROCESO
    ↓
COMPLETADO
```

### Flujo de rechazo

```text
PENDIENTE
    ↓
CANCELADO
```

También puede ocurrir:

```text
ACEPTADO
    ↓
CANCELADO
```

cuando el vendedor ya había aceptado el pedido pero posteriormente no puede concretarlo.

---

# 10. Modelo DetallePedido

Registra los productos específicos que forman parte de un pedido.

### Tabla

`detalle_pedido`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | PK | Identificador |
| `pedido_id` | FK | Pedido relacionado |
| `producto_id` | FK | Producto comprado |
| `cantidad` | INT | Cantidad solicitada |
| `precio_unitario` | DECIMAL(10,2) | Precio del producto al realizar el pedido |
| `subtotal` | DECIMAL(10,2) | Cantidad × precio unitario |

## Importancia del precio unitario

Se almacena el precio utilizado en el momento de realizar el pedido.

Ejemplo:

```text
Pedido #25

ESP32 DevKit V1
Cantidad: 2
Precio unitario: $180.00
Subtotal: $360.00
```

Si posteriormente el producto cambia a $220.00, el pedido anterior seguirá conservando el precio de $180.00.

---

# 11. Modelo Notificacion

Permite informar automáticamente a los usuarios sobre cambios importantes en los pedidos.

### Tabla

`notificacion`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | PK | Identificador |
| `usuario_id` | FK | Usuario que recibe la notificación |
| `pedido_id` | FK | Pedido relacionado |
| `tipo` | VARCHAR | Tipo de notificación |
| `titulo` | VARCHAR(150) | Título |
| `mensaje` | TEXT | Contenido |
| `leida` | BOOLEAN | Indica si fue leída |
| `fecha_creacion` | DATETIME | Fecha de creación |

## Tipos de notificación

```text
NUEVO_PEDIDO
PEDIDO_ACEPTADO
PEDIDO_RECHAZADO
PEDIDO_EN_PROCESO
PEDIDO_COMPLETADO
PEDIDO_CANCELADO
```

---

# 12. Relaciones entre entidades

```text
Usuario
   │
   ├────────────── 1 : 1 ──────── Carrito
   │
   ├────────────── 1 : N ──────── Pedido
   │
   └────────────── 1 : N ──────── Notificacion


Categoria
   │
   └────────────── 1 : N ──────── Producto
                                      │
                                      └──── 1 : 1 ──── Inventario


Carrito
   │
   └────────────── 1 : N ──────── ItemCarrito
                                      │
                                      └──── N : 1 ──── Producto


Pedido
   │
   ├────────────── 1 : N ──────── DetallePedido
   │                                  │
   │                                  └──── N : 1 ──── Producto
   │
   └────────────── 1 : N ──────── Notificacion
```

---

# 13. Flujo del inventario

El inventario funcionará mediante un sistema de reserva.

## Paso 1 — Cliente agrega productos

El producto permanece disponible porque solamente está en el carrito.

```text
Stock físico:      10
Stock reservado:    0
Disponible:        10
```

## Paso 2 — Cliente confirma el pedido

Se crea un pedido con estado:

```text
PENDIENTE
```

Y se reserva el producto:

```text
Stock físico:      10
Stock reservado:    2
Disponible:         8
```

## Paso 3 — Se notifica al vendedor

El vendedor recibe una notificación:

```text
Nuevo pedido recibido

Pedido #25
ESP32 DevKit V1 × 2
Total: $360.00
```

## Paso 4 — Vendedor acepta

El pedido cambia a:

```text
ACEPTADO
```

La reserva permanece.

## Paso 5 — Pedido en proceso

El vendedor puede cambiar el pedido a:

```text
EN_PROCESO
```

El cliente recibe una notificación.

## Paso 6 — Venta concretada

Cuando el vendedor entrega el producto y marca el pedido como completado:

```text
PEDIDO = COMPLETADO
```

El inventario se actualiza:

```text
Stock físico:       8
Stock reservado:    0
Disponible:         8
```

Las unidades dejan de estar reservadas y pasan a considerarse vendidas.

---

# 14. Flujo de rechazo

Si el vendedor no puede completar el pedido:

```text
PENDIENTE
    ↓
CANCELADO
```

El sistema debe:

1. Cambiar el estado del pedido a `CANCELADO`.
2. Registrar el motivo de cancelación.
3. Liberar las unidades reservadas.
4. Actualizar el inventario.
5. Crear una notificación para el cliente.

### Ejemplo

```text
Pedido #25

Estado:
CANCELADO

Motivo:
Producto no disponible.
```

El cliente recibe:

```text
Pedido #25 rechazado

El vendedor no pudo completar tu pedido.

Motivo:
Producto no disponible.
```

---

# 15. Flujo general del sistema

```text
                    CLIENTE
                       │
                       ▼
                 Ver producto
                       │
                       ▼
              Agregar al carrito
                       │
                       ▼
              Confirmar pedido
                       │
                       ▼
              Crear pedido
              Estado: PENDIENTE
                       │
                       ▼
              Reservar inventario
                       │
                       ▼
             Notificar al vendedor
                       │
                 ┌─────┴─────┐
                 │            │
                 ▼            ▼
              Aceptar      Rechazar
                 │            │
                 ▼            ▼
             ACEPTADO     CANCELADO
                 │            │
                 ▼            ├── Liberar reserva
             EN_PROCESO       │
                 │            └── Notificar cliente
                 ▼
              Entrega
                 │
                 ▼
             COMPLETADO
                 │
                 ▼
          Actualizar inventario
```

---

# 16. Entidades fuera del alcance de la versión 1

Para mantener el alcance original del proyecto, esta versión no contempla:

- Pagos en línea.
- Chat entre cliente y vendedor.
- Sistema de envíos/logística.
- Aplicación móvil.
- Sistema de recomendaciones.
- Cupones.
- Favoritos.
- Reseñas.
- Facturación electrónica.

Estas funcionalidades podrán evaluarse en futuras versiones.

---

# 17. Resumen de la estructura

| Modelo | Propósito |
|---|---|
| `auth_user` | Usuarios y autenticación |
| `Categoria` | Clasificación de productos |
| `Producto` | Catálogo |
| `Inventario` | Existencias y reservas |
| `Carrito` | Carrito del usuario |
| `ItemCarrito` | Productos del carrito |
| `Pedido` | Solicitud de compra y estado |
| `DetallePedido` | Productos de cada pedido |
| `Notificacion` | Avisos automáticos |

---

## 18. Próximo paso

Una vez aprobado este diseño, el siguiente paso será implementarlo en Django mediante:

```text
models.py
    ↓
makemigrations
    ↓
migrate
    ↓
MySQL
    ↓
Django Admin
    ↓
Datos de prueba
```

Este documento representa la **estructura inicial de la base de datos de TecnoMercado** y podrá modificarse conforme avance el desarrollo del proyecto.

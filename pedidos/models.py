from django.conf import settings
from django.core.validators import MinValueValidator
from django.db import models


class Carrito(models.Model):
    usuario = models.OneToOneField(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name="carrito"
    )
    fecha_creacion = models.DateTimeField(auto_now_add=True)
    fecha_actualizacion = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = "carrito"
        verbose_name = "Carrito"
        verbose_name_plural = "Carritos"

    def __str__(self):
        return f"Carrito de {self.usuario.username}"


class ItemCarrito(models.Model):
    carrito = models.ForeignKey(
        Carrito,
        on_delete=models.CASCADE,
        related_name="items"
    )
    producto = models.ForeignKey(
        "productos.Producto",
        on_delete=models.PROTECT,
        related_name="items_carrito"
    )
    cantidad = models.PositiveIntegerField(
        validators=[MinValueValidator(1)]
    )

    class Meta:
        db_table = "item_carrito"
        verbose_name = "Artículo del carrito"
        verbose_name_plural = "Artículos del carrito"
        constraints = [
            models.UniqueConstraint(
                fields=["carrito", "producto"],
                name="unique_producto_por_carrito"
            )
        ]

    def __str__(self):
        return f"{self.cantidad} x {self.producto.nombre}"


class Pedido(models.Model):

    class Estado(models.TextChoices):
        PENDIENTE = "PENDIENTE", "Pendiente"
        ACEPTADO = "ACEPTADO", "Aceptado"
        EN_PROCESO = "EN_PROCESO", "En proceso"
        COMPLETADO = "COMPLETADO", "Completado"
        CANCELADO = "CANCELADO", "Cancelado"

    usuario = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.PROTECT,
        related_name="pedidos"
    )
    estado = models.CharField(
        max_length=20,
        choices=Estado.choices,
        default=Estado.PENDIENTE
    )
    total = models.DecimalField(
        max_digits=10,
        decimal_places=2,
        default=0
    )
    fecha_creacion = models.DateTimeField(auto_now_add=True)
    fecha_actualizacion = models.DateTimeField(auto_now=True)
    fecha_completado = models.DateTimeField(
        blank=True,
        null=True
    )
    motivo_cancelacion = models.TextField(
        blank=True,
        null=True
    )

    class Meta:
        db_table = "pedido"
        verbose_name = "Pedido"
        verbose_name_plural = "Pedidos"
        ordering = ["-fecha_creacion"]

    def __str__(self):
        return f"Pedido #{self.id} - {self.estado}"


class DetallePedido(models.Model):
    pedido = models.ForeignKey(
        Pedido,
        on_delete=models.CASCADE,
        related_name="detalles"
    )
    producto = models.ForeignKey(
        "productos.Producto",
        on_delete=models.PROTECT,
        related_name="detalles_pedido"
    )
    cantidad = models.PositiveIntegerField(
        validators=[MinValueValidator(1)]
    )
    precio_unitario = models.DecimalField(
        max_digits=10,
        decimal_places=2
    )
    subtotal = models.DecimalField(
        max_digits=10,
        decimal_places=2
    )

    class Meta:
        db_table = "detalle_pedido"
        verbose_name = "Detalle del pedido"
        verbose_name_plural = "Detalles de los pedidos"

    def __str__(self):
        return f"{self.cantidad} x {self.producto.nombre}"


class Notificacion(models.Model):

    class Tipo(models.TextChoices):
        NUEVO_PEDIDO = "NUEVO_PEDIDO", "Nuevo pedido"
        PEDIDO_ACEPTADO = "PEDIDO_ACEPTADO", "Pedido aceptado"
        PEDIDO_RECHAZADO = "PEDIDO_RECHAZADO", "Pedido rechazado"
        PEDIDO_EN_PROCESO = "PEDIDO_EN_PROCESO", "Pedido en proceso"
        PEDIDO_COMPLETADO = "PEDIDO_COMPLETADO", "Pedido completado"
        PEDIDO_CANCELADO = "PEDIDO_CANCELADO", "Pedido cancelado"

    usuario = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name="notificaciones"
    )
    pedido = models.ForeignKey(
        Pedido,
        on_delete=models.CASCADE,
        related_name="notificaciones"
    )
    tipo = models.CharField(
        max_length=30,
        choices=Tipo.choices
    )
    titulo = models.CharField(max_length=150)
    mensaje = models.TextField()
    leida = models.BooleanField(default=False)
    fecha_creacion = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = "notificacion"
        verbose_name = "Notificación"
        verbose_name_plural = "Notificaciones"
        ordering = ["-fecha_creacion"]

    def __str__(self):
        return f"{self.titulo} - {self.usuario.username}"
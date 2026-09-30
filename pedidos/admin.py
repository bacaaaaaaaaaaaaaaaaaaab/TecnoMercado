from django.contrib import admin

from .models import (
    Carrito,
    ItemCarrito,
    Pedido,
    DetallePedido,
    Notificacion,
)


admin.site.register(Carrito)
admin.site.register(ItemCarrito)
admin.site.register(Pedido)
admin.site.register(DetallePedido)
admin.site.register(Notificacion)
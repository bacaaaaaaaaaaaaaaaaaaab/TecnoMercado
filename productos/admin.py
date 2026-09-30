from django.contrib import admin

from .models import Categoria, Producto, Inventario


@admin.register(Categoria)
class CategoriaAdmin(admin.ModelAdmin):
    list_display = ("nombre", "activo")
    list_filter = ("activo",)
    search_fields = ("nombre",)


@admin.register(Producto)
class ProductoAdmin(admin.ModelAdmin):
    list_display = (
        "nombre",
        "sku",
        "vendedor",
        "categoria",
        "precio",
        "activo",
        "fecha_creacion",
    )
    list_filter = (
        "categoria",
        "activo",
        "vendedor",
    )
    search_fields = (
        "nombre",
        "sku",
        "marca",
        "modelo",
    )


@admin.register(Inventario)
class InventarioAdmin(admin.ModelAdmin):
    list_display = (
        "producto",
        "stock_fisico",
        "stock_reservado",
        "stock_disponible",
        "stock_minimo",
        "actualizado",
    )
    list_filter = ("stock_minimo",)
    search_fields = (
        "producto__nombre",
        "producto__sku",
    )
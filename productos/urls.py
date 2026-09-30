from django.urls import path

from . import views


app_name = "productos"


urlpatterns = [
    path(
        "mis-productos/",
        views.mis_productos,
        name="mis_productos"
    ),

    path(
        "agregar/",
        views.agregar_producto,
        name="agregar_producto"
    ),

    path(
        "editar/<int:producto_id>/",
        views.editar_producto,
        name="editar_producto"
    ),
]
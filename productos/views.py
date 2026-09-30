from django.contrib.auth.decorators import login_required
from django.shortcuts import get_object_or_404, redirect, render

from .forms import ProductoForm
from .models import Producto


@login_required
def mis_productos(request):
    productos = Producto.objects.filter(
        vendedor=request.user
    ).order_by("-fecha_creacion")

    return render(
        request,
        "productos/mis_productos.html",
        {
            "productos": productos,
        }
    )


@login_required
def agregar_producto(request):
    if request.method == "POST":
        form = ProductoForm(request.POST, request.FILES)

        if form.is_valid():
            producto = form.save(commit=False)

            # El vendedor se asigna automáticamente
            producto.vendedor = request.user

            producto.save()

            return redirect("productos:mis_productos")
    else:
        form = ProductoForm()

    return render(
        request,
        "productos/agregar_producto.html",
        {
            "form": form,
        }
    )


@login_required
def editar_producto(request, producto_id):
    producto = get_object_or_404(
        Producto,
        id=producto_id,
        vendedor=request.user
    )

    if request.method == "POST":
        form = ProductoForm(
            request.POST,
            request.FILES,
            instance=producto
        )

        if form.is_valid():
            producto = form.save(commit=False)
            producto.vendedor = request.user
            producto.save()

            return redirect("productos:mis_productos")
    else:
        form = ProductoForm(instance=producto)

    return render(
        request,
        "productos/editar_producto.html",
        {
            "form": form,
            "producto": producto,
        }
    )
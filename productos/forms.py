from django import forms

from .models import Producto


class ProductoForm(forms.ModelForm):
    class Meta:
        model = Producto
        fields = [
            "categoria",
            "nombre",
            "descripcion",
            "marca",
            "modelo",
            "sku",
            "precio",
            "imagen",
        ]

        widgets = {
            "categoria": forms.Select(attrs={
                "class": "form-control",
            }),
            "nombre": forms.TextInput(attrs={
                "class": "form-control",
                "placeholder": "Nombre del producto",
            }),
            "descripcion": forms.Textarea(attrs={
                "class": "form-control",
                "placeholder": "Descripción del producto",
                "rows": 4,
            }),
            "marca": forms.TextInput(attrs={
                "class": "form-control",
                "placeholder": "Marca",
            }),
            "modelo": forms.TextInput(attrs={
                "class": "form-control",
                "placeholder": "Modelo",
            }),
            "sku": forms.TextInput(attrs={
                "class": "form-control",
                "placeholder": "Código SKU",
            }),
            "precio": forms.NumberInput(attrs={
                "class": "form-control",
                "placeholder": "Precio",
                "step": "0.01",
                "min": "0",
            }),
            "imagen": forms.ClearableFileInput(attrs={
                "class": "form-control",
            }),
        }
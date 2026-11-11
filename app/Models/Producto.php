<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'id_vendedor',
        'id_categoria',
        'nombre',
        'descripcion',
        'precio',
        'imagen_principal',
        'imagen_2',
        'imagen_3',
        'imagen_4',
        'imagen_5',
        'imagen_6',
        'imagen_7',
        'video',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    /**
     * Categoría a la que pertenece el producto.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(
            Categoria::class,
            'id_categoria',
            'id_categoria'
        );
    }

    /**
     * Vendedor propietario del producto.
     */
    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(
            Vendedor::class,
            'id_vendedor',
            'id_vendedor'
        );
    }

    /**
     * Inventario asociado al producto.
     */
    public function inventario(): HasOne
    {
        return $this->hasOne(
            Inventario::class,
            'id_producto',
            'id_producto'
        );
    }

    /**
     * Usuarios que tienen este producto en favoritos.
     */
    public function usuariosQueLoMarcaronFavorito(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'favoritos',
            'id_producto',
            'id_usuario',
            'id_producto',
            'id_usuario'
        )->using(Favorito::class)->withPivot('created_at');
    }


    /**
     * Artículos de carrito que hacen referencia a este producto.
     */
    public function itemsCarrito(): HasMany
    {
        return $this->hasMany(
            ItemCarrito::class,
            'id_producto',
            'id_producto'
        );
    }

    /**
     * Detalles de solicitudes en los que aparece este producto.
     */
    public function detallesPedido(): HasMany
    {
        return $this->hasMany(
            DetallePedido::class,
            'id_producto',
            'id_producto'
        );
    }
}

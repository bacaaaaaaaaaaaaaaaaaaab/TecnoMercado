<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCarrito extends Model
{
    protected $table = 'items_carrito';

    protected $primaryKey = 'id_item_carrito';

    protected $fillable = [
        'id_carrito',
        'id_producto',
        'cantidad',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
        ];
    }

    /**
     * Carrito al que pertenece el artículo.
     */
    public function carrito(): BelongsTo
    {
        return $this->belongsTo(
            Carrito::class,
            'id_carrito',
            'id_carrito'
        );
    }

    /**
     * Producto agregado al carrito.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(
            Producto::class,
            'id_producto',
            'id_producto'
        );
    }
}

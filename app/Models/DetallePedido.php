<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePedido extends Model
{
    protected $table = 'detalles_pedido';

    protected $primaryKey = 'id_detalle_pedido';

    protected $fillable = [
        'id_pedido_vendedor',
        'id_producto',
        'nombre_producto',
        'cantidad',
        'precio_unitario_referencia',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unitario_referencia' => 'decimal:2',
        ];
    }

    /**
     * Solicitud del vendedor a la que pertenece este detalle.
     */
    public function pedidoVendedor(): BelongsTo
    {
        return $this->belongsTo(
            PedidoVendedor::class,
            'id_pedido_vendedor',
            'id_pedido_vendedor'
        );
    }

    /**
     * Producto al que corresponde este detalle.
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'estado_respuesta',
        'disponibilidad_producto',
        'motivo_respuesta',
        'dar_de_baja_publicacion',
        'justificacion_publicacion',
        'fecha_respuesta',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unitario_referencia' => 'decimal:2',
            'disponibilidad_producto' => 'boolean',
            'dar_de_baja_publicacion' => 'boolean',
            'fecha_respuesta' => 'datetime',
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

    /**
     * Historial de respuestas registradas para este producto solicitado.
     */
    public function historialRespuestas(): HasMany
    {
        return $this->hasMany(
            HistorialRespuestaProducto::class,
            'id_detalle_pedido',
            'id_detalle_pedido'
        );
    }

    /**
     * Notificaciones relacionadas con este detalle del pedido.
     */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(
            Notificacion::class,
            'id_detalle_pedido',
            'id_detalle_pedido'
        );
    }
}

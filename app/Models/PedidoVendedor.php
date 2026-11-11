<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PedidoVendedor extends Model
{
    protected $table = 'pedidos_vendedor';

    protected $primaryKey = 'id_pedido_vendedor';

    protected $fillable = [
        'id_pedido',
        'id_vendedor',
        'subtotal_referencia',
        'estado',
        'fecha_respuesta',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_referencia' => 'decimal:2',
            'fecha_respuesta' => 'datetime',
        ];
    }

    /**
     * Pedido general al que pertenece esta solicitud.
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(
            Pedido::class,
            'id_pedido',
            'id_pedido'
        );
    }

    /**
     * Vendedor que recibe esta solicitud.
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
     * Detalles de productos incluidos en esta solicitud.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(
            DetallePedido::class,
            'id_pedido_vendedor',
            'id_pedido_vendedor'
        );
    }

    /**
     * Historial de cambios de estado de esta solicitud.
     */
    public function historialEstados(): HasMany
    {
        return $this->hasMany(
            HistorialEstadoPedido::class,
            'id_pedido_vendedor',
            'id_pedido_vendedor'
        );
    }

    /**
     * Notificaciones relacionadas con esta solicitud.
     */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(
            Notificacion::class,
            'id_pedido_vendedor',
            'id_pedido_vendedor'
        );
    }

}

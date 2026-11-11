<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $primaryKey = 'id_pedido';

    protected $fillable = [
        'id_comprador',
        'fecha_solicitud',
        'total_referencia',
        'estado_general',
    ];

    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'datetime',
            'total_referencia' => 'decimal:2',
        ];
    }


    /**
     * Comprador que realizó el pedido.
     */
    public function comprador(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_comprador',
            'id_usuario'
        );
    }

    /**
     * Solicitudes individuales generadas para los vendedores.
     */
    public function pedidosVendedor(): HasMany
    {
        return $this->hasMany(
            PedidoVendedor::class,
            'id_pedido',
            'id_pedido'
        );
    }
}

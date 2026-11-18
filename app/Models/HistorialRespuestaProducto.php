<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialRespuestaProducto extends Model
{
    protected $table = 'historial_respuestas_producto';

    protected $primaryKey = 'id_historial_respuesta';

    public const UPDATED_AT = null;

    protected $fillable = [
        'id_detalle_pedido',
        'id_usuario_responsable',
        'estado_anterior',
        'estado_nuevo',
        'disponibilidad_producto',
        'motivo_respuesta',
        'dar_de_baja_publicacion',
        'justificacion_publicacion',
        'fecha_respuesta',
    ];

    protected function casts(): array
    {
        return [
            'disponibilidad_producto' => 'boolean',
            'dar_de_baja_publicacion' => 'boolean',
            'fecha_respuesta' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Detalle del pedido cuya respuesta fue registrada.
     */
    public function detallePedido(): BelongsTo
    {
        return $this->belongsTo(
            DetallePedido::class,
            'id_detalle_pedido',
            'id_detalle_pedido'
        );
    }

    /**
     * Usuario responsable de registrar la respuesta.
     */
    public function usuarioResponsable(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario_responsable',
            'id_usuario'
        );
    }
}

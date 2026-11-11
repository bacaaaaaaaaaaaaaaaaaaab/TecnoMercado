<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventario extends Model
{
    protected $table = 'inventarios';

    protected $primaryKey = 'id_inventario';

    public const CREATED_AT = null;

    protected $fillable = [
        'id_producto',
        'stock_fisico',
        'stock_reservado',
    ];

    protected function casts(): array
    {
        return [
            'stock_fisico' => 'integer',
            'stock_reservado' => 'integer',
        ];
    }

    /**
     * Producto al que pertenece este inventario.
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

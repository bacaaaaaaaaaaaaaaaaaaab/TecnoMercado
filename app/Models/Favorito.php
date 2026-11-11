<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Favorito extends Pivot
{
    protected $table = 'favoritos';

    protected $fillable = [
        'id_usuario',
        'id_producto',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}

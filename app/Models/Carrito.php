<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrito extends Model
{
    protected $table = 'carritos';

    protected $primaryKey = 'id_carrito';

    protected $fillable = [
        'id_usuario',
        'estado',
    ];


    /*** Usuario propietario del carrito.*/
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }

    /*** Artículos que contiene el carrito.*/
    public function items(): HasMany
    {
        return $this->hasMany(
            ItemCarrito::class,
            'id_carrito',
            'id_carrito'
        );
    }
}

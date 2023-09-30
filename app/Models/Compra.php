<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;
    protected $table = 'compras';

    protected $fillable = [
        'user_id',
        'codigo',
        'proveedor_id',
        'fecha',
        'monto_pagado',
        'metodo_pago',
        'estado',
        'impuestos',
        'subtotal',
        'total',
        'descripcion',
    ];
}

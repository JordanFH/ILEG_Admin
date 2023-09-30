<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'compra_id',
        'tipo',
        'monto',
        'item_id',
        'cantidad',
        'precio',
        'observacion',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $fillable = [
        'cliente_id', 'fecha', 'descuento', 'impuestos', 'subtotal', 'total', 'monto_pagado', 'estado', 'codigo','metodo_pago','user_id'
    ];

    // Evento creating para generar el código antes de guardar el pedido
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pedido)
        {
            $ultimoPedido = self::latest('id')->first(); // Obtener el último pedido creado
            $n = ($ultimoPedido) ? intval(substr($ultimoPedido->codigo, 3)) + 1 : 1; // Incrementar el número en 1
            $pedido->codigo = 'V' . str_pad($n, 5, '0', STR_PAD_LEFT); // Formatear el código
        });
    }
}

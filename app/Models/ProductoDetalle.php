<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoDetalle extends Model
{
    use HasFactory;

    protected $table = 'producto_detalle';

    protected $fillable = ['producto_id', 'detalle_pedido_id', 'precio_unitario', 'cantidad'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function detallePedido()
    {
        return $this->belongsTo(DetallePedido::class, 'detalle_pedido_id');
    }
}

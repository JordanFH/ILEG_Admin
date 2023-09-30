<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioDetalle extends Model
{
    use HasFactory;

    protected $table = 'servicio_detalle';

    protected $fillable = ['servicio_id', 'detalle_pedido_id', 'precio_unitario', 'cantidad'];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function detallePedido()
    {
        return $this->belongsTo(DetallePedido::class, 'detalle_pedido_id');
    }
}

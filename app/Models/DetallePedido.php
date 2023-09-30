<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DetallePedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id', 'monto','tipo', 'item_id','cantidad'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function producto_detalle()
    {
        return $this->belongsTo(ProductoDetalle::class, 'producto_id');
    }

    public function servicio_detalle(){
        return $this->belongsTo(ServicioDetalle::class, 'servicio_id');
    }
    /*public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto_servicio')
            ->withPivot('cantidad_producto')
            ->withTimestamps();
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'pedido_producto_servicio')
            ->withPivot('cantidad_servicio')
            ->withTimestamps();
    }*/

    /*public static function boot()
    {
        parent::boot();

        static::created(function ($detallePedido) {
            // Verificar si el detallePedido tiene productos
            if ($detallePedido->productos->count() > 0) {
                // Validar el stock antes de crear la relación
                foreach ($detallePedido->productos as $producto) {
                    $cantidadProducto = $producto->pivot->cantidad_producto;

                    // Verificar si hay suficiente stock para el producto
                    if ($producto->stock < $cantidadProducto) {
                        throw new Exception("No hay suficiente stock de {$producto->nombre}.");
                    }

                    // Reducir el stock del producto
                    $producto->stock -= $cantidadProducto;
                    $producto->save();
                }
            }
        });

        static::deleted(function ($detallePedido) {
            // Restaurar el stock de productos al eliminar la relación
            foreach ($detallePedido->productos as $producto) {
                $producto->stock += $producto->pivot->cantidad_producto;
                $producto->save();
            }
        });

        static::saving(function ($detallePedido) {
            $rules = [
                'pedido_id' => 'required',
                'precio_unitario' => 'required|numeric|min:0',
                'cantidad' => 'required|integer|min:1',
            ];

            $validator = Validator::make($detallePedido->toArray(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }
        });
    }*/
}

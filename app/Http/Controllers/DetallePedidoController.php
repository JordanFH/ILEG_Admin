<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoDetalle;
use App\Models\ServicioDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DetallePedidoController extends Controller
{

    public function __construct()
    {
        // Solo permitir a los usuarios con el rol de administrador acceder a todas estas rutas
        $this->middleware('can:ventas')->only('index');
        $this->middleware('can:ventas.create')->only('create', 'store');
        $this->middleware('can:ventas.edit')->only('edit', 'update');
        $this->middleware('can:ventas.destroy')->only('destroy');
        $this->middleware('can:ventas.show')->only('show');
        $this->middleware('can:ventas.store')->only('store');
        $this->middleware('can:ventas.update')->only('update');
    }

    // Guardar Detalle Pedido

    /**
     * @throws ValidationException
     */

    public function show()
    {
        //no se esta usando
        $select = DB::table('detalle_pedidos')
            ->join('pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->join('producto_detalle', 'pedidos.id', '=', 'producto_detalle.producto_id')
            ->join('productos', 'producto_detalle.producto_id', '=', 'productos.id')
            ->select('*')
            ->get();
        return view('viewTest')->with('select', $select);
    }

    public function store(Request $request)
    {

        //validaciones
        //nos aseguramos que el item_id no sea null, es decir haya un producto o servicio seleccionado
        if ($request->input('item_id') == null){
            return "el item_id es null";
        }
        //validamos que el stock sea suficiente
        if ($request->input('tipo') == 'Producto'){
            $producto = Producto::find($request->input("item_id"));
            if ($producto->stock < $request->input("cantidad")) {
                return "el stock es 0"; // se debe enviar un mensaje de error
            }
        }

        /*$precio_base = $request->input("monto") / 1.18;
        $precio_base_rounded = round($precio_base, 2);*/


        //creamos el detalle pedido
        $detalle_pedido = DetallePedido::create([
            // 'pedido_id' => $pedido->id,
            'tipo' => $request->input("tipo"),
            'monto' => $request->input("monto"),
            'item_id' => $request->input("item_id"),
            'cantidad' => $request->input("cantidad"),
            // ...
        ]);

        if($request->input('tipo') == 'Producto'){
            $producto->stock = $producto->stock - $request->input("cantidad");
            $producto->save();
        }
        return $detalle_pedido;
    }

    public function storeDetalle(Request $request,$id){
        //validaciones
        //nos aseguramos que el item_id no sea null, es decir haya un producto o servicio seleccionado
        if ($request->input('item_id') == null){
            return "el item_id es null";
        }
        //validamos que el stock sea suficiente
        $producto = Producto::find($request->input("item_id"));
        if ($producto->stock < $request->input("cantidad")) {
            return "el stock es 0"; // se debe enviar un mensaje de error
        }
//        $data = $request->validate([
//            'cantidad' => 'required|integer|min:1',
//            'precio_unitario' => 'required|numeric|min:0',
//        ]);



        //creamos el detalle pedido
        $detalle_pedido = DetallePedido::create([
            'pedido_id' => $id,
            'tipo' => $request->input("tipo"),
            'monto' => $request->input("monto"),
            'item_id' => $request->input("item_id"),
            'cantidad' => $request->input("cantidad"),
            // ...
        ]);
        $pedido = Pedido::find($id);
        $impuestoUni = $detalle_pedido->monto/1.18 * 0.18;
        $pedido->total += $detalle_pedido->monto/1.18 + $impuestoUni;
        $pedido->subtotal += $detalle_pedido->monto/1.18;
        $pedido->impuestos += $impuestoUni ;
        $pedido->save();

        if($request->input('tipo') == 'Producto'){
            $producto->stock = $producto->stock - $request->input("cantidad");
            $producto->save();
        }

        return $detalle_pedido;
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'pedido_id' => 'exists:pedidos,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        DetallePedido::find($id)->update($data);

        return redirect()->route('pedidos.edit');
    }

    // Eliminar Pedido

    public function destroy($id)
    {
        $detalle_pedido = DetallePedido::find($id);
        $response = $detalle_pedido;
        if($detalle_pedido->pedido_id != null){
            try {
                //hay que actualizar el pedido
                $pedido = Pedido::find($detalle_pedido->pedido_id);

                // Calcula los montos y ajustes antes de actualizar el pedido
                $detalle_monto = $detalle_pedido->monto;
                $detalle_impuesto = $detalle_pedido->monto/1.18 * 0.18;

                // Actualiza los valores del pedido
                $pedido->total -= ($detalle_monto/1.18 + $detalle_impuesto);
                $pedido->impuestos -= $detalle_impuesto;
                $pedido->subtotal -= $detalle_monto/1.18;

                // Si el pedido ya no tiene productos/servicios, restablece el descuento
                $tolerancia = 0.0001; // Tolerancia permitida para las comparaciones
                if (abs($pedido->subtotal) < $tolerancia) {
                    $pedido->descuento = 0;
                    $pedido->total = 0;
                }
                if($detalle_pedido->tipo =="Producto"){
                    $producto = Producto::where('id',$detalle_pedido->item_id)->first();
                    $producto->stock = $producto->stock + $detalle_pedido->cantidad;
                    $producto->save();
                }

                $pedido->save();

                $detalle_pedido->delete();
            } catch (\Exception $e) {
                // Maneja la excepción de acuerdo a tus necesidades
                return "ERROR";
                //return response()->json(['message' => 'Error al eliminar el detalle de pedido'], 500);
            }
        }else{
            if($detalle_pedido->tipo =="Producto"){
                $producto = Producto::where('id',$detalle_pedido->item_id)->first();
                $producto->stock = $producto->stock + $detalle_pedido->cantidad;
                $producto->save();
            }
            $detalle_pedido->delete();

        }
        return $response;


        //este codigo es para el editar pedido
    }
}

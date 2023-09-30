<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Producto;
use Illuminate\Http\Request;

class CompraDetalleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:compras')->only('index');
        $this->middleware('can:compras.create')->only('create', 'store');
        $this->middleware('can:compras.edit')->only('edit', 'update');
        $this->middleware('can:compras.destroy')->only('destroy');
        $this->middleware('can:compras.show')->only('show');
        $this->middleware('can:compras.store')->only('store');
        $this->middleware('can:compras.update')->only('update');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        //validaciones
        //nos aseguramos que el item_id no sea null, es decir haya un producto o servicio seleccionado
        if ($request->input('item_id') == null){
            return "el item_id es null";
        }


        /*$precio_base = $request->input("monto") / 1.18;
        $precio_base_rounded = round($precio_base, 2);*/

        //creamos el detalle pedido
        $detalle_compra = CompraDetalle::create([
            // 'pedido_id' => $pedido->id,
            'monto' => $request->input("monto"),
            'item_id' => $request->input("item_id"),
            'cantidad' => $request->input("cantidad"),
            // ...
        ]);

        $producto = Producto::find($request->input("item_id"));

        $producto->stock = $producto->stock + $request->input("cantidad");
        $producto->save();


        return $detalle_compra;
    }

    /**
     * Display the specified resource.
     */
    public function show(CompraDetalle $compraDetalle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompraDetalle $compraDetalle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, CompraDetalle $compraDetalle)
    {
        //
    }

    public function storeDetalle(Request $request,$id){
        //validaciones
        //nos aseguramos que el item_id no sea null, es decir haya un producto o servicio seleccionado
        if ($request->input('item_id') == null){
            return "el item_id es null";
        }
        //creamos el detalle pedido
        $detalle_compra = CompraDetalle::create([
            'compra_id' => $id,
            'monto' => $request->input("monto"),
            'item_id' => $request->input("item_id"),
            'cantidad' => $request->input("cantidad"),
            // ...
        ]);
        $compra = Compra::find($id);
        $impuestoUni = $detalle_compra->monto/1.18 * 0.18;
        $compra->total += $detalle_compra->monto/1.18 + $impuestoUni;
        $compra->subtotal += $detalle_compra->monto/1.18;
        $compra->impuestos += $impuestoUni ;
        $compra->save();

        $producto = Producto::find($request->input("item_id"));
        $producto->stock = $producto->stock + $request->input("cantidad");
        $producto->save();


        return $detalle_compra;
    }

    public function destroy($id)
    {
        $detalle_compra = CompraDetalle::find($id);
        $response = $detalle_compra;
        //actualizar precios
        if($detalle_compra->compra_id != null){
            try {
                //hay que actualizar el compra
                $compra = Compra::find($detalle_compra->compra_id);

                // Calcula los montos y ajustes antes de actualizar el pedido
                $detalle_monto = $detalle_compra->monto;
                $detalle_impuesto = $detalle_compra->monto/1.18 * 0.18;

                // Actualiza los valores del pedido
                $compra->total -= ($detalle_monto/1.18 + $detalle_impuesto);
                $compra->impuestos -= $detalle_impuesto;
                $compra->subtotal -= $detalle_monto/1.18;

                // Si el pedido ya no tiene productos/servicios, restablece el descuento
                $tolerancia = 0.0001; // Tolerancia permitida para las comparaciones
                if (abs($compra->subtotal) < $tolerancia) {
                    $compra->descuento = 0;
                    $compra->total = 0;
                }
                $compra->save();
                $detalle_compra->delete();
                return $response;
            } catch (\Exception $e) {
                // Maneja la excepción de acuerdo a tus necesidades
                return "ERROR";
                //return response()->json(['message' => 'Error al eliminar el detalle de pedido'], 500);
            }
        }

        //actualizar stock en Productos
        $producto = Producto::find($detalle_compra->item_id);
        $producto->stock = $producto->stock - $detalle_compra->cantidad;
        $producto->save();
        $detalle_compra->delete();
        return $response;

    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\DetalleCompraCollection;
use App\Http\Resources\ProductoCollection;
use App\Http\Resources\ProveedorCollection;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompraController extends Controller
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
        $compras = Compra::orderBy('codigo', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        $proveedores = Proveedor::all();

        return Inertia::render('Compras/Index', [
            'compras' => $compras,
            'proveedores' => $proveedores->map(function ($proveedor) {
                return [
                    'id' => $proveedor->id,
                    'nombre' => $proveedor->nombre,
                ];
            })->toArray()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Compras/Create', [
            'proveedores' => new ProveedorCollection(
                Proveedor::all()
            ),
            'productos' => new ProductoCollection(
                Producto::orderBy('categoria_id', 'asc')
                    ->groupBy('categoria_id', 'nombre')
                    ->orderBy('nombre', 'asc')
                    ->get()
            ),
            'detalle_compras' => new DetalleCompraCollection(
                CompraDetalle::whereNull('compra_id')->get()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        //para el subtotal como cambia si hay descuento se tiene que aplicar esto: pedidoData.total- pedidoData.impuestos + pedidoData.descuentoCalculado
        $subtotal = $request->input('total') - $request->input('impuestos');
        //creamos el pedido
        $compra = Compra::create([
            'user_id' => auth()->user()->id,
            'proveedor_id' => $request->input('proveedor_id'),
            'fecha' => $request->input('fecha'),
            'impuestos' => $request->input('impuestos'),
            'subtotal' => $subtotal,
            'total' => $request->input('total'),
            'monto_pagado' => $request->input('monto_pagado'),
            'estado' => $request->input('estado'),
            'descripcion' => $request->input('descripcion'),
            'metodo_pago' => $request->input('metodo_pago'),
        ]);


        //actualimos todos los detalle pedidos que estaban a null porque ya se asignaron

        CompraDetalle::where('compra_id', null)
            ->update(['compra_id' => $compra->id]);

        return redirect()->route('compras.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Compra $compra)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Compra $compra)
    {

        // Buscar pedido por ID
        $user = User::find($compra->user_id);

        return Inertia::render('Compras/Edit', [
            'user' => $user,
            'compra' => $compra,
            'proveedores' => new ProveedorCollection(
                Proveedor::orderBy('nombreEmpresa', 'asc')
                    ->get()
            ),
            'detalle_compras' => new DetalleCompraCollection(
                CompraDetalle::where('compra_id',$compra->id)->get()
            ),
            'productos' => new ProductoCollection(
                Producto::orderBy('categoria_id', 'asc')
                    ->groupBy('categoria_id', 'nombre')
                    ->orderBy('nombre', 'asc')
                    ->get()
            ),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Compra $compra)
    {

        $compra->update(
            [
                'proveedor_id' => $request->input('proveedor_id'),
                'fecha' => $request->input('fecha'),
                'impuestos' => $request->input('impuestos'),
                'subtotal' => $request->input('subtotal'),
                'total' => $request->input('total'),
                'monto_pagado' => $request->input('monto_pagado'),
                'estado' => $request->input('estado'),
                'user_id' => auth()->user()->id,
                'descripcion' => $request->input('descripcion'),
                'metodo_pago' => $request->input('metodo_pago'),
            ]
        );

        return redirect()->route('compras.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Compra $compra)
    {
        $detallesCompra = CompraDetalle::where('compra_id', $compra->id)->get();
        foreach ($detallesCompra as $detalleCompra) {
            $producto = Producto::find($detalleCompra->item_id);
            $producto->stock = $producto->stock - $detalleCompra->cantidad;
            $producto->save();
            $detalleCompra->delete();
        }
        $compra->delete();
        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClienteCollection;
use App\Http\Resources\DetallePedidoCollection;
use App\Http\Resources\ProductoCollection;
use App\Http\Resources\ServicioCollection;
use App\Models\Cliente;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Closure;


class PedidoController extends Controller
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

    // Listar Pedidos

    public function index()
    {
        $pedidos = Pedido::orderBy('codigo', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();
        $clientes = Cliente::all();

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos,
            'clientes' => $clientes->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre,
                ];
            })->toArray()
        ]);
    }

    // Crear Pedido

    public function create()
    {
        return Inertia::render('Pedidos/Create', [
            'clientes' => new ClienteCollection(
                Cliente::orderBy('empresa', 'asc')
                    ->groupBy('empresa', 'nombre')
                    ->orderBy('nombre', 'asc')
                    ->get()
            ),
            'productos' => new ProductoCollection(
                Producto::orderBy('categoria_id', 'asc')
                    ->groupBy('categoria_id', 'nombre')
                    ->orderBy('nombre', 'asc')
                    ->get()
            ),
            'servicios' => new ServicioCollection(
                Servicio::orderBy('categoria_id', 'asc')
                    ->groupBy('categoria_id', 'nombre')
                    ->orderBy('nombre', 'asc')
                    ->get()
            ),
            'detalle_pedidos' => new DetallePedidoCollection(
                DetallePedido::whereNull('pedido_id')->get()
            ),
        ]);
    }

    // Guardar Pedido

    /**
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required',
            'descuento' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $subtotal = $request->input('subtotal');
                    $impuestos = $subtotal * 0.18;
                    $totalConImpuestos = $subtotal + $impuestos;
                    $totalConDescuento = $totalConImpuestos - $totalConImpuestos * ($value / 100);

                    $montoPagado = $request->input('monto_pagado');

                    if ($montoPagado > $totalConDescuento) {
                        $fail('La suma del descuento y el monto pagado no puede ser mayor que el total.');
                    }
                },
            ],
            'impuestos' => 'required',
            'total' => 'required',
            'monto_pagado' => [
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $subtotal = $request->input('subtotal');
                    $descuento = $request->input('descuento');
                    $impuestos = $subtotal * 0.18;
                    $totalConImpuestos = $subtotal + $impuestos;
                    $totalConDescuento = $totalConImpuestos - $totalConImpuestos * ($descuento / 100);

                    if ($value > $totalConDescuento) {
                        $fail('La suma del descuento y el monto pagado no puede ser mayor que el total.');
                    }
                },
            ],
            'estado' => 'required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        //para el subtotal como cambia si hay descuento se tiene que aplicar esto: pedidoData.total- pedidoData.impuestos + pedidoData.descuentoCalculado
        $subtotal = $request->input('total') - $request->input('impuestos') + $request->input('descuentoCalculado');
        //creamos el pedido
        $pedido = Pedido::create([
            'user_id' => auth()->user()->id,
            'cliente_id' => $request->input('cliente_id'),
            'fecha' => $request->input('fecha'),
            'descuento' => $request->input('descuentoCalculado'),
            'impuestos' => $request->input('impuestos'),
            'subtotal' => $subtotal,
            'total' => $request->input('total'),
            'monto_pagado' => $request->input('monto_pagado'),
            'estado' => $request->input('estado'),
        ]);

        //actualimos todos los detalle pedidos que estaban a null porque ya se asignaron

        DetallePedido::where('pedido_id', null)
            ->update(['pedido_id' => $pedido->id]);

        return redirect()->route('pedidos.index');
    }

    // Editar Pedido

    public function edit($id)
    {
        // Buscar pedido por ID
        $pedido = Pedido::find($id);
        $user = User::find($pedido->user_id);

        return Inertia::render('Pedidos/Edit', [
            'user' => $user,
            'pedido' => $pedido,
            'clientes' => new ClienteCollection(
                Cliente::orderBy('empresa', 'asc')
                    ->groupBy('empresa', 'nombre')
                    ->orderBy('nombre', 'asc')
                    ->get()
            ),
            'detalle_pedidos' => new DetallePedidoCollection(
                DetallePedido::where('pedido_id',$id)->get()
            ),
            'productos' => new ProductoCollection(
                Producto::orderBy('categoria_id', 'asc')
                    ->groupBy('categoria_id', 'nombre')
                    ->orderBy('nombre', 'asc')
                    ->get()
            ),
            'servicios' => new ServicioCollection(
                Servicio::orderBy('categoria_id', 'asc')
                    ->groupBy('categoria_id', 'nombre')
                    ->orderBy('nombre', 'asc')
                    ->get()
            ),
        ]);
    }

    // Actualizar Pedido

    /**
     * @throws ValidationException
     */
    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required',
            'descuento' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $subtotal = $request->input('subtotal');
                    $impuestos = $subtotal * 0.18;
                    $totalConImpuestos = $subtotal + $impuestos;
                    $totalConDescuento = $totalConImpuestos - $totalConImpuestos * ($value / 100);

                    $montoPagado = $request->input('monto_pagado');

                    if ($montoPagado > $totalConDescuento) {
                        $fail('La suma del descuento y el monto pagado no puede ser mayor que el total.');
                    }
                },
            ],
            'impuestos' => 'required',
            'total' => 'required',
            'monto_pagado' => [
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $subtotal = $request->input('subtotal');
                    $descuento = $request->input('descuento');
                    $impuestos = $subtotal * 0.18;
                    $totalConImpuestos = $subtotal + $impuestos;
                    $totalConDescuento = $totalConImpuestos - $totalConImpuestos * ($descuento / 100);

                    if ($value > $totalConDescuento) {
                        $fail('La suma del descuento y el monto pagado no puede ser mayor que el total.');
                    }
                },
            ],
            'estado' => 'required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }


        $pedido = Pedido::find($id);
        $pedido->update(
            [
                'cliente_id' => $request->input('cliente_id'),
                'fecha' => $request->input('fecha'),
                'descuento' => $request->input('descuento'),
                'impuestos' => $request->input('impuestos'),
                'subtotal' => $request->input('subtotal'),
                'total' => $request->input('total'),
                'monto_pagado' => $request->input('monto_pagado'),
                'estado' => $request->input('estado'),
                'user_id' => auth()->user()->id,
            ]
        );

        return redirect()->route('pedidos.index');
    }

    // Eliminar Pedido

    public function destroy($id)
    {

        //el id no esta llegando por eso falla, luego esta funcionando
        $pedido = Pedido::find($id);
        if (!$pedido) {
            abort(404);
        }
        // Eliminar los registros relacionados en la tabla DetallePedido
        if($pedido->estado == 'Pendiente'){
            $detallePedido = DetallePedido::where('pedido_id', $pedido->id)->get();
            foreach ($detallePedido as $detalle) {
                //actualizar stock en productos
                if($detalle->tipo == 'Producto'){
                    $producto = Producto::find($detalle->item_id);
                    $producto->stock = $producto->stock + $detalle->cantidad;
                    $producto->save();
                }
                $detalle->delete();
            }

        }else{
            $detallePedido = DetallePedido::where('pedido_id', $pedido->id)->get();
            foreach ($detallePedido as $detalle) {
                $detalle->delete();
            }
        }

        //Eliminar los Servicios o Productos Detalles que se encuentran en DetallePedido
//        $serviciosDetalle = ServicioDetalle::where('detalle_pedido_id', $detallePedido->id)->get();
//
//        foreach ($serviciosDetalle as $servicio_detalle) {
//            $servicio_detalle->delete();
//        }
//
//        $productosDetalle = ProductoDetalle::where('detalle_pedido_id', $detallePedido->id)->get();
//        foreach ($productosDetalle as $producto_detalle) {
//            $producto_detalle->delete();
//        }


        //$detallePedido->delete();

        // Eliminar el pedido en la tabla Pedidos


        $pedido->delete();

        // Devolver una respuesta de éxito
        return response()->noContent();

        /*$pedido = Pedido::find($id);

        if (!$pedido) {
            abort(404);
        }

        $pedido->delete();

        return response()->noContent();
        */
    }

}

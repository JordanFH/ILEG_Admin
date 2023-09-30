<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    public function __construct()
    {
        // Solo permitir a los usuarios con el rol de administrador acceder a todas estas rutas
        $this->middleware('can:proveedores')->only('index');
        $this->middleware('can:proveedores.create')->only('create', 'store');
        $this->middleware('can:proveedores.edit')->only('edit', 'update');
        $this->middleware('can:proveedores.destroy')->only('destroy');
        $this->middleware('can:proveedores.show')->only('show');
        $this->middleware('can:proveedores.store')->only('store');
        $this->middleware('can:proveedores.update')->only('update');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Proveedores/Index',[
            'proveedores' => Proveedor::orderBy('nombreEmpresa', 'asc')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Proveedores/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombreEmpresa' => 'required|unique:' . Proveedor::class,
            'nombreContacto' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'ruc' => 'required',
        ]);
        Proveedor::create($request->all());
        return redirect()->route('proveedores.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $proveedor = Proveedor::find($id);
        return Inertia::render('Proveedores/Edit', [
            'proveedor' => $proveedor
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombreEmpresa' => 'required|unique:' . Proveedor::class,
            'nombreContacto' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'ruc' => 'required',
        ]);
        Proveedor::find($id)->update($request->all());
        return redirect()->route('proveedores.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            abort(404);
        }

        $proveedor->delete();

        return response()->noContent();
    }
}

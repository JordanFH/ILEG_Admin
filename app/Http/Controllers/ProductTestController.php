<?php

namespace App\Http\Controllers;

use App\Events\StockDisponible;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductTestController extends Controller
{
    public function __construct()
    {
        // Solo permitir a los usuarios con el rol de administrador acceder a todas estas rutas
        $this->middleware('role:Admin');
    }

    public function index(){
        $products = Producto::all();
        return view('home')->with('products',$products);
    }
    public function actualizar(Request $request)
    {
        $product = Producto::where('nombre',$request->id)->first();
        $product->stock = $request->input('stock');
        $product->update();
        event(new StockDisponible($product));
        return redirect('/home');
    }
}

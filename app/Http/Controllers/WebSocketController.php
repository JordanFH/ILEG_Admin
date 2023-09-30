<?php

namespace App\Http\Controllers;

use App\Events\CategoriaEvent;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebSocketController extends Controller
{
    public function __construct()
    {
        // Solo permitir a los usuarios con el rol de administrador acceder a todas estas rutas
        $this->middleware('role:Admin');
    }

    public function index()
    {
        $categorias = Categoria::orderBy('tipo', 'asc')
            ->groupBy('tipo', 'nombre')
            ->orderBy('nombre', 'asc')
            ->get();
        $categoria = "Categoría enviada desde el backend";

        event(new CategoriaEvent($categoria));

        return response()->json(['message' => 'Categoria enviada exitosamente']);
        return Inertia::render('viewTestWS', ['categorias' => $categorias]);
    }
}

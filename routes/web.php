<?php

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\CompraDetalleController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\CotizacionDetalleController;
use App\Http\Controllers\DetallePedidoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProductTestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReportCotizacionController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/home',[\App\Http\Controllers\ProductTestController::class, 'actualizar'])->name('actualizar');
Route::post('/', [ProductTestController::class, 'actualizar'])->name('actualizar');
Route::get('/actualizar', function () {
    return view('actualizar');
});
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    //Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('categorias', CategoriaController::class);
    Route::resource('servicios', ServicioController::class)->names('servicios');
    Route::resource('productos', ProductoController::class)->names('productos');
    Route::resource('clientes', ClienteController::class)->names('clientes');
    Route::resource('proveedores', ProveedorController::class)->names('proveedores');
    Route::resource('ventas', PedidoController::class)->names('pedidos');
    Route::resource('compras', CompraController::class)->names('compras');
    Route::resource('compradetalles', CompraDetalleController::class); //->only(['store', 'update', 'destroy']);
    Route::resource('detalles', DetallePedidoController::class); //->only(['store', 'update', 'destroy']);
    Route::resource('roles', RoleController::class)->names('roles');
    // Ruta adicional para storeDetalle
    Route::post('/detalles/store/{id}', [DetallePedidoController::class,'storeDetalle'])->name('detalles.storeDetalle');
    Route::post('/compradetalles/store/{id}', [CompraDetalleController::class,'storeDetalle'])->name('compradetalles.storeDetalle');

    //para cotizacion
    Route::resource('cotizaciones', CotizacionController::class)->names('cotizaciones');
    Route::get('/report/cotizacion', [ReportCotizacionController::class,'report'])->name('cotizaciones.report');
    Route::get('/report/cotizacion/edit/{cotizacion_id}', [ReportCotizacionController::class, 'reportEdit'])->name('cotizaciones.edit.report');
    Route::resource('cotizaciondetalles', CotizacionDetalleController::class); //->only(['store', 'update', 'destroy']);
    Route::post('/cotizaciones/detalles/{id}', [CotizacionDetalleController::class,'storeDetalle'])->name('cotizaciones.storeDetalle');
});

require __DIR__ . '/auth.php';

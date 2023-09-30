<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proveedors', function (Blueprint $table) {
            $table->id();
            $table->string('nombreEmpresa', 200)->unique();
            $table->string('nombreContacto', 200);
            $table->string('direccion', 50);
            $table->string('telefono', 11);
            $table->string('email', 100)->nullable();
            $table->string('sitioWeb', 100)->nullable();
            $table->string('ruc', 11);
            $table->string('tipoProveedor', 50)->nullable();
            $table->string('descripcion', 200)->nullable();
            $table->string('tipoCuenta', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedors');
    }
};

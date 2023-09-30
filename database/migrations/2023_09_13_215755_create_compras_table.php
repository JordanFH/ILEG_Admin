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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->date('fecha')->default(now());
            $table->float('impuestos')->default(0.0);
            $table->float('subtotal')->default(0.0);
            $table->float('total')->default(0.0);
            $table->float('monto_pagado')->default(0.0);
            $table->string('estado');
            $table->string('metodo_pago')->default('Efectivo')->nullable();
            $table->string('descripcion',150)->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('proveedor_id')->references('id')->on('proveedors');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};

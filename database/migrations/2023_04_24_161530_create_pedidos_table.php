<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('pedidos')) {
            return;
        }

        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cliente_id');
            $table->date('fecha')->default(now());
            $table->integer('descuento')->default(0);
            $table->float('impuestos')->default(0.0);
            $table->float('subtotal')->default(0.0);
            $table->float('total')->default(0.0);
            $table->float('monto_pagado')->default(0.0);
            $table->string('estado');
            $table->string('metodo_pago')->default('efectivo');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('cliente_id')->references('id')->on('clientes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};

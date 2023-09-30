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
        if (Schema::hasTable('producto_detalle')) {
            return;
        }

        Schema::create('producto_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('detalle_pedido_id')->nullable();
            $table->float('precio_unitario')->default(0.0); //inncecesario si se tiene el precio en la tabla de productos
            $table->unsignedInteger('cantidad')->default(1);
            $table->timestamps();

            // Definir relación con la tabla de productos
            //$table->foreign('producto_id')->references('id')->on('productos');

            // Definir relación con la tabla de detalle_pedidos
            //$table->foreign('detalle_pedido_id')->references('id')->on('detalle_pedidos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_detalle');
    }
};

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
        Schema::create('compra_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compra_id')->nullable();
            //Es necesario agregar que producto o servicio se esta agregando
            //$table->string('tipo',10)->default('Producto'); Innecesario, porque solo se compra productos
            $table->unsignedBigInteger('item_id')->default(0);
            $table->float('monto')->default(0.0);
            $table->integer('cantidad')->default(0);

            $table->foreign('compra_id')->references('id')->on('compras')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra_detalles');
    }
};

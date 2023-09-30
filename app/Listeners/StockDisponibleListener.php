<?php

namespace App\Listeners;

use App\Events\StockDisponible;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StockDisponibleListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(StockDisponible $event): void
    {
        $product = $event->product;
        var_dump("El producto {$product->nombre} tiene stock disponible");
    }
}

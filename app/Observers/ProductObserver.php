<?php

namespace App\Observers;

use App\Events\StockDisponible;
use App\Models\Producto;

class ProductObserver
{
    public function updated(Producto $product){
        $oldStock = $product -> getOriginal('stock');
        $newStock = $product -> stock;
        //event(new StockDisponible($product));
        if($oldStock != $newStock){
            //event(new StockDisponible($product));
        }
    }
}

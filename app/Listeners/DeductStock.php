<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DeductStock
{
    public function handle(OrderCreated $event): void
    {
        foreach ($event->order->items as $item) {
            $product = Product::lockForUpdate()->find($item->product_id);

            if ($product->stock < $item->quantity) {
                throw new \RuntimeException(
                    "Insufficient stock for product '{$product->name}'. Available: {$product->stock}."
                );
            }

            $product->decrement('stock', $item->quantity);
        }
    }
}

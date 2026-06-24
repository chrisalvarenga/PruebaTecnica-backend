<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderItem;

class OrderItemObserver
{
    public function created(OrderItem $orderItem): void
    {
        $this->recalculateTotal($orderItem->order_id);
    }

    public function updated(OrderItem $orderItem): void
    {
        $this->recalculateTotal($orderItem->order_id);
    }

    public function deleted(OrderItem $orderItem): void
    {
        $this->recalculateTotal($orderItem->order_id);
    }

    private function recalculateTotal(int $orderId): void
    {
        $total = OrderItem::where('order_id', $orderId)->sum('subtotal');
        Order::where('id', $orderId)->update(['total' => $total]);
    }
}

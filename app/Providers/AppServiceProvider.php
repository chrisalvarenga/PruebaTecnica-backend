<?php

namespace App\Providers;

use App\Models\OrderItem;
use App\Observers\OrderItemObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        OrderItem::observe(OrderItemObserver::class);
    }
}

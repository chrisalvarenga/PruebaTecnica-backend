<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrderOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $order = $request->route('order');

        if (!$order instanceof Order || $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}

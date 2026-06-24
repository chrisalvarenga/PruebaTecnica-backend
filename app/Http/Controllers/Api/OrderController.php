<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function store(CreateOrderRequest $request): JsonResponse|OrderResource
    {
        try {
            $order = DB::transaction(function () use ($request) {
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'total' => 0,
                    'status' => 'pending',
                ]);

                foreach ($request->items as $item) {
                    $product = Product::find($item['product_id']);

                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                        'subtotal' => $product->price * $item['quantity'],
                    ]);
                }

                OrderCreated::dispatch($order->load('items'));

                return $order->fresh('items.product');
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load('items.product'));
    }

    public function cancel(Order $order): JsonResponse|OrderResource
    {
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be cancelled.',
            ], 422);
        }

        $order->update(['status' => 'cancelled']);

        return new OrderResource($order);
    }
}

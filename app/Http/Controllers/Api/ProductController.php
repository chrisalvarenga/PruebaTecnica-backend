<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $products = Cache::remember('products.all', 300, fn () => Product::all());

        return ProductResource::collection($products);
    }
}

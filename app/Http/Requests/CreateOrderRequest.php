<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $item) {
                $product = Product::find($item['product_id'] ?? null);
                if ($product && $product->stock < ($item['quantity'] ?? 0)) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        "Insufficient stock for product '{$product->name}'. Available: {$product->stock}."
                    );
                }
            }
        });
    }
}

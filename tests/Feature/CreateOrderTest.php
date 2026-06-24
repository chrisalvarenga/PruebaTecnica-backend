<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50.00, 'stock' => 10]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.status', 'pending');
        $response->assertJsonPath('data.total', '150.00');

        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_order_fails_when_stock_is_insufficient(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 20.00, 'stock' => 2]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertEquals(2, $product->fresh()->stock);
    }

    public function test_unauthenticated_user_cannot_create_order(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_cancel_another_users_order(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        Sanctum::actingAs($owner);
        $createResponse = $this->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);
        $orderId = $createResponse->json('data.id');

        Sanctum::actingAs($other);
        $this->putJson("/api/orders/{$orderId}/cancel")->assertStatus(403);
    }

    public function test_cannot_cancel_a_completed_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        Sanctum::actingAs($user);
        $createResponse = $this->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);
        $orderId = $createResponse->json('data.id');

        \App\Models\Order::find($orderId)->update(['status' => 'completed']);

        $this->putJson("/api/orders/{$orderId}/cancel")->assertStatus(422);
    }
}

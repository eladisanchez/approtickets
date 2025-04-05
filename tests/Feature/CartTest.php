<?php

namespace ApproTickets\Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use ApproTickets\Models\Product;

it('shows the cart', function () {
    $response = $this->get('cistell');
    $response->assertStatus(200);
    $response->assertSee('Cistell');
});

it('adds a product to the cart', function () {
    $response = $this->post('cistell', [
        'product' => 1,
        'qty' => [1]
    ]);
    $response->assertStatus(302);
    $response->assertSessionHas('itemAdded', true);
});

it("should not add a product if qty is lower than minimum", function () {
    $product = Product::find(1);
    $product->min_tickets = 2;
    $product->save();
    $response = $this->post('cistell', [
        'product' => 1,
        'qty' => [1]
    ]);
    $response->assertStatus(302);
    $response->assertSessionHas('error', 'approtickets::cart.min_tickets');
});

it("should not add a product if qty is greater than max", function () {
    $product = Product::find(1);
    $product->max_tickets = 1;
    $product->save();
    $response = $this->post('cistell', [
        'product' => 1,
        'qty' => [4]
    ]);
    $response->assertStatus(302);
    $response->assertSessionHas('error', 'approtickets::cart.max_tickets');
});
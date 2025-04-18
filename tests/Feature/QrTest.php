<?php

namespace ApproTickets\Tests\Feature;

use ApproTickets\Models\Booking;
use ApproTickets\Models\User;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $user = User::where('name', 'lector')->first();
    actingAs($user);
});

describe('auth', function () {

    test('login', function () {
        $response = $this->post('api/login', [
            'email' => 'lector@entradessolsones.com',
            'password' => 'lector1234'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'token_type', 'user']);
    });

    test('login with wrong credentials', function () {
        $response = $this->post('api/login', [
            'email' => 'vLq0K@example.com',
            'password' => '12345678'
        ]);
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Credencials incorrectes']);
    });

});

describe('qr check', function () {

    // Codi mal format, no té 3 parts separades amb guió baix
    test('malformed qr code', function () {
        $response = $this->post('api/checkQR', [
            'qr' => base64_encode('123456789')
        ]);
        $response->assertStatus(403);
        $response->assertJson(['message' => 'El codi no és vàlid']);
    });

    // Codi de comanda inexistent
    test('wrong qr code', function () {
        $response = $this->post('api/checkQR', [
            'qr' => base64_encode('1_2_3')
        ]);
        $response->assertStatus(403);
        $response->assertJson(['message' => 'El codi no és correcte']);
    });

    // L'usuari no té permís per escanejar el codi
    test('user without permission', function () {
        $userClient = User::find(3);
        actingAs($userClient);
        $response = $this->post('api/checkQR', [
            'qr' => base64_encode('xx_1234_1_1')
        ]);
        $response->assertStatus(403);
        $response->assertJson(['message' => 'El codi no correspon a l\'esdeveniment']);
    });

    // Codi correcte
    test('correct qr code', function () {
        $response = $this->post('api/checkQR', [
            'qr' => base64_encode('xx_1234_1_1')
        ]);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Codi correcte']);
        $this->assertDatabaseHas('scans', [
            'booking_id' => '1',
            'scan_id' => 1,
        ]);
    });

    // Esdeveniment passat
    test('past event', function () {
        $booking = Booking::find(1);
        $booking->day = date('Y-m-d', strtotime('-1 day'));
        $booking->save();
        $response = $this->post('api/checkQR', [
            'qr' => base64_encode('xx_1234_1_1')
        ]);
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Aquest codi ja no és vàlid']);
    });

    // Esdeveniment futur
    test('future event', function () {
        $booking = Booking::find(1);
        $booking->day = date('Y-m-d', strtotime('+2 day'));
        $booking->save();
        $response = $this->post('api/checkQR', [
            'qr' => base64_encode('xx_1234_1_1')
        ]);
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Falten 2 dies per l\'espectacle']);
    });

    // Ja utilitzat
    test('used qr code', function () {
        $response = $this->post('api/checkQR', [
            'qr' => base64_encode('xx_1234_1_1')
        ]);
        $response = $this->post('api/checkQR', [
            'qr' => base64_encode('xx_1234_1_1')
        ]);
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Aquest codi ja s\'ha utilitzat']);
    });

});
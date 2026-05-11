<?php

use Illuminate\Support\Facades\Route;

test('api hides internal exception message when debug is disabled', function () {
    config(['app.debug' => false]);

    Route::get('/api/_test/internal-error', function () {
        throw new RuntimeException('SQLSTATE[HY000]: database password leaked');
    });

    $response = $this->getJson('/api/_test/internal-error');

    $response->assertStatus(500)
        ->assertJson([
            'status' => 'error',
            'message' => 'Internal Server Error.',
        ]);
});

test('api keeps explicit client error message for business exceptions', function () {
    config(['app.debug' => false]);

    Route::get('/api/_test/client-error', function () {
        throw new Exception('Transaksi ini sedang diproses.', 409);
    });

    $response = $this->getJson('/api/_test/client-error');

    $response->assertStatus(409)
        ->assertJson([
            'status' => 'error',
            'message' => 'Transaksi ini sedang diproses.',
        ]);
});

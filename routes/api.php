<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/hello', function () {
    return response()->json([
        'message' => 'Hello World',
    ]);
});

Route::get('/tickets', [TicketController::class, 'index']);
Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
    ->whereNumber('ticket');
Route::post('/tickets', [TicketController::class, 'store']);

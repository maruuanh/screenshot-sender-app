<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncomingMessageController;

Route::get('/', [IncomingMessageController::class, 'index']);
Route::get('/webhook', [IncomingMessageController::class, 'verifyWebhook']); // untuk verifikasi awal
Route::post('/webhook', [IncomingMessageController::class, 'handleWebhook']);

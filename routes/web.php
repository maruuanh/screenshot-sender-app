<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncomingMessageController;

Route::get('/', [IncomingMessageController::class, 'index']);
Route::post('/webhook', [IncomingMessageController::class, 'handleWebhook']);

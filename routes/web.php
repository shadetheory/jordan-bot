<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\SpeakController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ChatController::class, 'index'])->name('chat');
Route::post('/ask', [ChatController::class, 'store'])->middleware('throttle:ask')->name('ask.store');
Route::post('/asks/{ask}/speak', SpeakController::class)->middleware('throttle:30,1')->name('asks.speak');

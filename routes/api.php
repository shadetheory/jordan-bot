<?php

use App\Http\Controllers\Api\AskController;
use Illuminate\Support\Facades\Route;

Route::post('/ask', AskController::class)->middleware('throttle:ask');

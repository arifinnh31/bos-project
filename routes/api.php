<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GineeWebhookController;

Route::post('/ginee/webhook', [GineeWebhookController::class, 'handle']);

<?php

use App\Http\Controllers\Internal\ChannelController;
use Illuminate\Support\Facades\Route;

Route::middleware('stream-server.token')->prefix('internal')->group(function () {
    Route::get('/channels', [ChannelController::class, 'index']);
    Route::get('/channels/{slug}/logo', [ChannelController::class, 'logo']);
});

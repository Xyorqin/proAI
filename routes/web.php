<?php

use App\Http\Controllers\Telegram\TelegramController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


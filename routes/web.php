<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

Route::get('/login', function () {
    return redirect('/painel/login');
})->name('login');

Route::get('/', function () {
    return Auth::check() ? redirect('/painel') : view('landing');
});

// Healthcheck endpoint for Render
Route::get('/healthz', function () {
    return response()->json(['status' => 'ok'], 200);
});

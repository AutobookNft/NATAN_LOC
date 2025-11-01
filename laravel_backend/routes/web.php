<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// NATAN Chat Interface
Route::get('/natan/chat', function () {
    return view('natan.chat');
})->name('natan.chat');

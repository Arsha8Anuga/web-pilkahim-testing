<?php

use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Route;

Route::get('/login', Login::class)->name("auth.login");

Route::get('/', function () {
    return view('welcome');
})->name("dahboard");

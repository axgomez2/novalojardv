<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Este arquivo contém apenas rotas básicas.
| O painel admin está em routes/admin.php
| A API para o frontend Vue SPA (rdv2026) está em routes/api.php
*/

// Redirecionar raiz para o admin
Route::get('/', function () {
    return redirect()->route('admin.login');
})->name('home');

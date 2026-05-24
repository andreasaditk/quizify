<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizifyController; // <-- PASTIKAN BARIS INI ADA DAN SAMA PERSIS

Route::get('/', [QuizifyController::class, 'index']);
Route::post('/api/generate', [QuizifyController::class, 'generate']);